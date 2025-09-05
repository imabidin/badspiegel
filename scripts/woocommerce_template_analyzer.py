#!/usr/bin/env python3
"""
WooCommerce Template Analyzer
Analysiert die WooCommerce Templates im Theme und vergleicht sie mit den Original WooCommerce Templates
"""

import os
import re
import hashlib
from pathlib import Path
from dataclasses import dataclass
from typing import Dict, List, Optional, Tuple
import difflib

@dataclass
class TemplateInfo:
    """Information über ein Template"""
    file_path: str
    version: Optional[str] = None
    last_modified: Optional[str] = None
    size: int = 0
    hash: str = ""
    is_custom: bool = False
    has_modifications: bool = False

class WooCommerceTemplateAnalyzer:
    def __init__(self, theme_path: str, woocommerce_path: str):
        self.theme_path = Path(theme_path)
        self.woocommerce_path = Path(woocommerce_path)
        self.theme_templates: Dict[str, TemplateInfo] = {}
        self.wc_templates: Dict[str, TemplateInfo] = {}

    def get_file_hash(self, file_path: Path) -> str:
        """Berechnet MD5 Hash einer Datei"""
        try:
            with open(file_path, 'rb') as f:
                return hashlib.md5(f.read()).hexdigest()
        except:
            return ""

    def extract_template_version(self, file_path: Path) -> Optional[str]:
        """Extrahiert die Template-Version aus dem PHP-Header"""
        try:
            with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
                content = f.read(2000)  # Nur ersten Teil lesen

            # Suche nach @version Kommentar
            version_match = re.search(r'@version\s+([0-9.]+)', content, re.IGNORECASE)
            if version_match:
                return version_match.group(1)

            # Fallback: Suche nach anderen Version-Patterns
            version_patterns = [
                r'Version:\s*([0-9.]+)',
                r'version\s*=\s*["\']([0-9.]+)["\']',
                r'WC\s+([0-9.]+)',
            ]

            for pattern in version_patterns:
                match = re.search(pattern, content, re.IGNORECASE)
                if match:
                    return match.group(1)

        except Exception as e:
            print(f"Fehler beim Lesen von {file_path}: {e}")

        return None

    def get_relative_template_path(self, full_path: Path, base_path: Path) -> str:
        """Gibt den relativen Pfad eines Templates zurück"""
        try:
            return str(full_path.relative_to(base_path))
        except ValueError:
            return str(full_path)

    def scan_templates(self, base_path: Path, template_dict: Dict[str, TemplateInfo], prefix: str = ""):
        """Scannt alle PHP-Templates in einem Verzeichnis"""
        if not base_path.exists():
            print(f"Warnung: Pfad {base_path} existiert nicht")
            return

        woocommerce_dir = base_path / "woocommerce"
        if not woocommerce_dir.exists():
            print(f"Warnung: WooCommerce Template-Verzeichnis {woocommerce_dir} existiert nicht")
            return

        for file_path in woocommerce_dir.rglob("*.php"):
            if file_path.is_file():
                relative_path = self.get_relative_template_path(file_path, woocommerce_dir)

                template_info = TemplateInfo(
                    file_path=str(file_path),
                    version=self.extract_template_version(file_path),
                    size=file_path.stat().st_size,
                    hash=self.get_file_hash(file_path),
                    last_modified=str(file_path.stat().st_mtime)
                )

                template_dict[relative_path] = template_info
                print(f"{prefix}Gefunden: {relative_path} (Version: {template_info.version or 'unbekannt'})")

    def analyze_templates(self):
        """Führt die komplette Template-Analyse durch"""
        print("=== WooCommerce Template Analyzer ===\n")

        print("1. Scanne Theme-Templates...")
        self.scan_templates(self.theme_path, self.theme_templates, "Theme: ")

        print(f"\n2. Scanne WooCommerce Core-Templates...")
        wc_templates_path = self.woocommerce_path / "templates"
        if wc_templates_path.exists():
            for file_path in wc_templates_path.rglob("*.php"):
                if file_path.is_file():
                    relative_path = self.get_relative_template_path(file_path, wc_templates_path)

                    template_info = TemplateInfo(
                        file_path=str(file_path),
                        version=self.extract_template_version(file_path),
                        size=file_path.stat().st_size,
                        hash=self.get_file_hash(file_path),
                        last_modified=str(file_path.stat().st_mtime)
                    )

                    self.wc_templates[relative_path] = template_info

        print(f"WooCommerce Core: {len(self.wc_templates)} Templates gefunden")

        print(f"\n3. Analysiere Template-Status...")
        self.compare_templates()

    def compare_templates(self):
        """Vergleicht Theme-Templates mit WooCommerce Core-Templates"""
        print("\n=== TEMPLATE ANALYSE ERGEBNISSE ===\n")

        # 1. Templates die nur im Theme existieren (Custom Templates)
        custom_templates = []

        # 2. Templates die veraltet sind
        outdated_templates = []

        # 3. Templates die identisch sind
        identical_templates = []

        # 4. Templates die modifiziert wurden
        modified_templates = []

        # 5. Templates mit gleicher Version aber unterschiedlichem Content
        version_mismatch_templates = []

        for theme_template_path, theme_info in self.theme_templates.items():
            if theme_template_path not in self.wc_templates:
                # Template existiert nur im Theme
                custom_templates.append((theme_template_path, theme_info))
            else:
                wc_info = self.wc_templates[theme_template_path]

                # Hash-Vergleich für identische Dateien
                if theme_info.hash == wc_info.hash:
                    identical_templates.append((theme_template_path, theme_info, wc_info))
                else:
                    # Dateien sind unterschiedlich
                    if theme_info.version and wc_info.version:
                        if theme_info.version == wc_info.version:
                            # Gleiche Version aber unterschiedlicher Content
                            version_mismatch_templates.append((theme_template_path, theme_info, wc_info))
                        elif self.is_version_older(theme_info.version, wc_info.version):
                            # Theme-Template ist veraltet
                            outdated_templates.append((theme_template_path, theme_info, wc_info))
                        else:
                            # Template wurde modifiziert
                            modified_templates.append((theme_template_path, theme_info, wc_info))
                    else:
                        # Keine Versionsinformation verfügbar
                        modified_templates.append((theme_template_path, theme_info, wc_info))

        # Ausgabe der Ergebnisse
        self.print_results(custom_templates, outdated_templates, identical_templates,
                          modified_templates, version_mismatch_templates)

    def is_version_older(self, version1: str, version2: str) -> bool:
        """Prüft ob version1 älter als version2 ist"""
        try:
            v1_parts = [int(x) for x in version1.split('.')]
            v2_parts = [int(x) for x in version2.split('.')]

            # Gleiche Länge für Vergleich
            max_len = max(len(v1_parts), len(v2_parts))
            v1_parts.extend([0] * (max_len - len(v1_parts)))
            v2_parts.extend([0] * (max_len - len(v2_parts)))

            return v1_parts < v2_parts
        except:
            return False

    def print_results(self, custom_templates, outdated_templates, identical_templates,
                     modified_templates, version_mismatch_templates):
        """Gibt die Analyseergebnisse aus"""

        print("🔍 CUSTOM TEMPLATES (nur im Theme vorhanden):")
        print("=" * 60)
        if custom_templates:
            for template_path, info in custom_templates:
                print(f"📄 {template_path}")
                print(f"   Version: {info.version or 'unbekannt'}")
                print(f"   Größe: {info.size} bytes")
                print()
        else:
            print("✅ Keine Custom Templates gefunden\n")

        print("⚠️  VERALTETE TEMPLATES:")
        print("=" * 60)
        if outdated_templates:
            for template_path, theme_info, wc_info in outdated_templates:
                print(f"📄 {template_path}")
                print(f"   Theme Version: {theme_info.version} → WC Version: {wc_info.version}")
                print(f"   ⚡ UPDATE ERFORDERLICH!")
                print()
        else:
            print("✅ Keine veralteten Templates gefunden\n")

        print("⚠️  VERSION MISMATCH (gleiche Version, unterschiedlicher Inhalt):")
        print("=" * 80)
        if version_mismatch_templates:
            for template_path, theme_info, wc_info in version_mismatch_templates:
                print(f"📄 {template_path}")
                print(f"   Version: {theme_info.version} = {wc_info.version}")
                print(f"   Theme Hash: {theme_info.hash[:8]}...")
                print(f"   WC Hash: {wc_info.hash[:8]}...")
                print(f"   🔍 MANUELL PRÜFEN - möglicherweise benutzerdefinierte Änderungen")
                print()
        else:
            print("✅ Keine Version-Mismatches gefunden\n")

        print("🔧 MODIFIZIERTE TEMPLATES:")
        print("=" * 60)
        if modified_templates:
            for template_path, theme_info, wc_info in modified_templates:
                print(f"📄 {template_path}")
                print(f"   Theme Version: {theme_info.version or 'unbekannt'}")
                print(f"   WC Version: {wc_info.version or 'unbekannt'}")
                print(f"   📝 Hat benutzerdefinierte Änderungen")
                print()
        else:
            print("✅ Keine modifizierten Templates gefunden\n")

        print("✅ IDENTISCHE TEMPLATES:")
        print("=" * 60)
        if identical_templates:
            for template_path, theme_info, wc_info in identical_templates[:5]:  # Nur erste 5 zeigen
                print(f"📄 {template_path} (Version: {theme_info.version or 'unbekannt'})")
            if len(identical_templates) > 5:
                print(f"... und {len(identical_templates) - 5} weitere identische Templates")
            print()
        else:
            print("⚠️ Keine identischen Templates gefunden\n")

        # Zusammenfassung
        print("📊 ZUSAMMENFASSUNG:")
        print("=" * 60)
        print(f"🎨 Custom Templates: {len(custom_templates)}")
        print(f"⚠️  Veraltete Templates: {len(outdated_templates)}")
        print(f"🔧 Modifizierte Templates: {len(modified_templates)}")
        print(f"⚠️  Version Mismatches: {len(version_mismatch_templates)}")
        print(f"✅ Identische Templates: {len(identical_templates)}")
        print(f"📁 Gesamt Theme Templates: {len(self.theme_templates)}")

def main():
    # Pfade definieren
    theme_path = "/home/imabidin/badspiegel/wordpress/wp-content/themes/bsawesome"
    woocommerce_path = "/home/imabidin/badspiegel/wordpress/wp-content/plugins/woocommerce"

    # Prüfe ob Pfade existieren
    if not os.path.exists(theme_path):
        print(f"❌ Theme-Pfad nicht gefunden: {theme_path}")
        return

    if not os.path.exists(woocommerce_path):
        print(f"❌ WooCommerce-Pfad nicht gefunden: {woocommerce_path}")
        return

    # Analyzer starten
    analyzer = WooCommerceTemplateAnalyzer(theme_path, woocommerce_path)
    analyzer.analyze_templates()

if __name__ == "__main__":
    main()
