#!/usr/bin/env python3
"""
Version Mismatch Analyzer
Analysiert Templates mit gleicher Version aber unterschiedlichem Inhalt
"""

import os
import re
import hashlib
from pathlib import Path
from dataclasses import dataclass
from typing import Dict, List, Optional, Tuple
import difflib

class VersionMismatchAnalyzer:
    def __init__(self, theme_path: str, woocommerce_path: str):
        self.theme_path = Path(theme_path)
        self.woocommerce_path = Path(woocommerce_path)

    def analyze_sample_mismatches(self):
        """Analysiert eine Auswahl der Version-Mismatch Templates"""
        print("=== ANALYSE VON VERSION-MISMATCH TEMPLATES ===\n")

        # Sample der wichtigsten Templates die untersucht werden sollen
        sample_templates = [
            'content-product.php',
            'archive-product.php',
            'single-product.php',
            'single-product/add-to-cart/simple.php',
            'single-product/add-to-cart/variable.php',
            'checkout/form-checkout.php',
            'checkout/payment.php',
            'cart/cart-totals.php',
            'myaccount/dashboard.php',
            'loop/add-to-cart.php',
            'global/breadcrumb.php',
            'notices/error.php'
        ]

        print("🔍 UNTERSUCHUNG VON BEISPIEL-TEMPLATES:")
        print("=" * 80)

        customizations_found = []
        minor_changes = []
        significant_changes = []

        for template_path in sample_templates:
            result = self.analyze_single_mismatch(template_path)
            if result:
                if result['type'] == 'significant':
                    significant_changes.append(result)
                elif result['type'] == 'customization':
                    customizations_found.append(result)
                else:
                    minor_changes.append(result)

        # Ergebnisse ausgeben
        print("\n🎨 TEMPLATES MIT BENUTZERDEFINIERTEN ANPASSUNGEN:")
        print("=" * 80)
        if customizations_found:
            for result in customizations_found:
                print(f"📄 {result['template']}")
                print(f"   Version: {result['version']}")
                print(f"   Anpassungstyp: {result['customization_type']}")
                print(f"   Änderungen: {result['changes_summary']}")
                print()
        else:
            print("✅ Keine eindeutigen benutzerdefinierten Anpassungen in Stichprobe gefunden\n")

        print("🔧 TEMPLATES MIT BEDEUTENDEN ÄNDERUNGEN:")
        print("=" * 80)
        if significant_changes:
            for result in significant_changes:
                print(f"📄 {result['template']}")
                print(f"   Version: {result['version']}")
                print(f"   Änderungsumfang: {result['changes_count']} Zeilen")
                print(f"   Bereiche: {result['changes_summary']}")
                print(f"   💡 {result['recommendation']}")
                print()
        else:
            print("✅ Keine bedeutenden Änderungen in Stichprobe gefunden\n")

        print("📝 TEMPLATES MIT KLEINEN ÄNDERUNGEN:")
        print("=" * 80)
        if minor_changes:
            for result in minor_changes[:5]:  # Nur erste 5 zeigen
                print(f"📄 {result['template']} - {result['changes_summary']}")
            if len(minor_changes) > 5:
                print(f"... und {len(minor_changes) - 5} weitere mit ähnlichen kleinen Änderungen")
            print()
        else:
            print("✅ Keine kleinen Änderungen in Stichprobe gefunden\n")

        # Zusammenfassung und Empfehlungen
        self.provide_mismatch_recommendations(customizations_found, significant_changes, minor_changes)

    def analyze_single_mismatch(self, template_path: str) -> Optional[Dict]:
        """Analysiert ein einzelnes Template mit Version-Mismatch"""
        theme_file = self.theme_path / "woocommerce" / template_path
        wc_file = self.woocommerce_path / "templates" / template_path

        if not theme_file.exists() or not wc_file.exists():
            return None

        try:
            with open(theme_file, 'r', encoding='utf-8', errors='ignore') as f:
                theme_content = f.readlines()

            with open(wc_file, 'r', encoding='utf-8', errors='ignore') as f:
                wc_content = f.readlines()

            # Version extrahieren
            version = self.extract_template_version(theme_file)

            # Diff erstellen
            diff = list(difflib.unified_diff(
                theme_content, wc_content,
                fromfile=f'Theme',
                tofile=f'WooCommerce',
                n=2
            ))

            if len(diff) <= 4:  # Keine signifikanten Unterschiede
                return None

            # Analyse der Änderungen
            changes_analysis = self.analyze_changes_detail(diff, theme_content)

            # Klassifizierung
            if changes_analysis['is_customization']:
                return {
                    'template': template_path,
                    'version': version or 'unbekannt',
                    'type': 'customization',
                    'customization_type': changes_analysis['customization_type'],
                    'changes_summary': changes_analysis['summary'],
                    'changes_count': changes_analysis['total_changes']
                }
            elif changes_analysis['total_changes'] > 20:
                return {
                    'template': template_path,
                    'version': version or 'unbekannt',
                    'type': 'significant',
                    'changes_count': changes_analysis['total_changes'],
                    'changes_summary': changes_analysis['summary'],
                    'recommendation': changes_analysis['recommendation']
                }
            else:
                return {
                    'template': template_path,
                    'version': version or 'unbekannt',
                    'type': 'minor',
                    'changes_count': changes_analysis['total_changes'],
                    'changes_summary': changes_analysis['summary']
                }

        except Exception as e:
            print(f"   ❌ Fehler beim Analysieren von {template_path}: {e}")
            return None

    def extract_template_version(self, file_path: Path) -> Optional[str]:
        """Extrahiert die Template-Version aus dem PHP-Header"""
        try:
            with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
                content = f.read(2000)

            version_match = re.search(r'@version\s+([0-9.]+)', content, re.IGNORECASE)
            if version_match:
                return version_match.group(1)
        except:
            pass
        return None

    def analyze_changes_detail(self, diff_lines: List[str], theme_content: List[str]) -> Dict:
        """Detaillierte Analyse der Änderungen"""
        analysis = {
            'total_changes': 0,
            'html_changes': 0,
            'php_changes': 0,
            'css_changes': 0,
            'custom_code': 0,
            'is_customization': False,
            'customization_type': '',
            'summary': '',
            'recommendation': ''
        }

        added_lines = []
        removed_lines = []

        for line in diff_lines:
            if line.startswith('+') and not line.startswith('+++'):
                added_lines.append(line[1:].strip())
                analysis['total_changes'] += 1
            elif line.startswith('-') and not line.startswith('---'):
                removed_lines.append(line[1:].strip())
                analysis['total_changes'] += 1

        # Analysiere Theme-spezifische Indikatoren
        theme_content_str = ''.join(theme_content).lower()
        custom_indicators = [
            'bsawesome', 'custom', 'theme_', 'my_', 'custom_',
            'override', 'modification', 'anpassung'
        ]

        custom_found = any(indicator in theme_content_str for indicator in custom_indicators)

        # Analysiere hinzugefügte Zeilen
        for line in added_lines:
            line_lower = line.lower()

            if any(indicator in line_lower for indicator in custom_indicators):
                analysis['custom_code'] += 1
                analysis['is_customization'] = True

            if '<' in line and '>' in line:
                analysis['html_changes'] += 1
            elif '$' in line or 'function' in line_lower or 'if ' in line_lower:
                analysis['php_changes'] += 1
            elif 'class=' in line_lower or 'style=' in line_lower:
                analysis['css_changes'] += 1

        # Bestimme Customization-Typ
        if analysis['is_customization']:
            if analysis['css_changes'] > analysis['php_changes']:
                analysis['customization_type'] = 'Styling/CSS-Anpassungen'
            elif analysis['custom_code'] > 0:
                analysis['customization_type'] = 'Benutzerdefinierte Funktionalität'
            else:
                analysis['customization_type'] = 'Theme-spezifische Änderungen'

        # Erstelle Zusammenfassung
        summary_parts = []
        if analysis['html_changes'] > 0:
            summary_parts.append(f"HTML: {analysis['html_changes']}")
        if analysis['php_changes'] > 0:
            summary_parts.append(f"PHP: {analysis['php_changes']}")
        if analysis['css_changes'] > 0:
            summary_parts.append(f"CSS: {analysis['css_changes']}")
        if analysis['custom_code'] > 0:
            summary_parts.append(f"Custom Code: {analysis['custom_code']}")

        analysis['summary'] = ', '.join(summary_parts) if summary_parts else 'Unbekannte Änderungen'

        # Empfehlung
        if analysis['total_changes'] > 50:
            analysis['recommendation'] = 'Detaillierte manuelle Prüfung erforderlich'
        elif analysis['php_changes'] > 10:
            analysis['recommendation'] = 'Funktionalität nach Update testen'
        else:
            analysis['recommendation'] = 'Visuell prüfen nach Update'

        return analysis

    def provide_mismatch_recommendations(self, customizations, significant, minor):
        """Gibt Empfehlungen für den Umgang mit Version-Mismatches"""
        print("💡 EMPFEHLUNGEN FÜR VERSION-MISMATCH TEMPLATES:")
        print("=" * 80)

        total_mismatches = 91  # Aus der ersten Analyse
        sample_size = len(customizations) + len(significant) + len(minor)

        print(f"📊 Stichprobe: {sample_size} von {total_mismatches} Templates analysiert")
        print()

        if customizations:
            print("🎨 FÜR BENUTZERDEFINIERTE ANPASSUNGEN:")
            print("   1. ✅ Diese Templates BEHALTEN - sie enthalten gewünschte Anpassungen")
            print("   2. 📋 Anpassungen dokumentieren für zukünftige Updates")
            print("   3. 🔍 Regelmäßig prüfen ob WooCommerce-Updates Konflikte verursachen")
            print()

        if significant:
            print("🔧 FÜR TEMPLATES MIT BEDEUTENDEN ÄNDERUNGEN:")
            print("   1. ⚠️  Vorsichtig prüfen - möglicherweise wichtige Unterschiede")
            print("   2. 🧪 In Staging-Umgebung WooCommerce-Version testen")
            print("   3. 📝 Unterschiede dokumentieren und bewerten")
            print("   4. 🔄 Je nach Ergebnis updaten oder beibehalten")
            print()

        print("📋 ALLGEMEINE STRATEGIE FÜR ALLE VERSION-MISMATCHES:")
        print("   1. 🚀 Erstelle ein Test-System mit aktuellen WooCommerce-Templates")
        print("   2. 🎨 Teste alle wichtigen Funktionen (Checkout, Cart, Product Pages)")
        print("   3. 👀 Prüfe visuell auf Design-Unterschiede")
        print("   4. ⚖️  Entscheide pro Template: Update vs. Beibehalten")
        print("   5. 📚 Führe Liste der bewusst nicht aktualisierten Templates")
        print()

        print("🚨 WARNUNG:")
        print("   • Version-Mismatches können auf unbeabsichtigte Änderungen hinweisen")
        print("   • Möglicherweise wurden Templates manuell editiert und nicht dokumentiert")
        print("   • Manche 'Anpassungen' könnten veraltete Fixes sein, die nicht mehr nötig sind")

def main():
    theme_path = "/home/imabidin/badspiegel/wordpress/wp-content/themes/bsawesome"
    woocommerce_path = "/home/imabidin/badspiegel/wordpress/wp-content/plugins/woocommerce"

    analyzer = VersionMismatchAnalyzer(theme_path, woocommerce_path)
    analyzer.analyze_sample_mismatches()

if __name__ == "__main__":
    main()
