#!/usr/bin/env python3
"""
Detaillierte WooCommerce Template Diff-Analyse
Erstellt detaillierte Vergleiche für kritische Templates
"""

import os
import re
import hashlib
from pathlib import Path
from dataclasses import dataclass
from typing import Dict, List, Optional, Tuple
import difflib

class DetailedTemplateAnalyzer:
    def __init__(self, theme_path: str, woocommerce_path: str):
        self.theme_path = Path(theme_path)
        self.woocommerce_path = Path(woocommerce_path)

        # Kritische Templates die besondere Aufmerksamkeit brauchen
        self.critical_templates = [
            'cart/cart.php',
            'cart/mini-cart.php',
            'checkout/form-checkout.php',
            'checkout/payment.php',
            'single-product/add-to-cart/variable.php',
            'single-product/add-to-cart/simple.php',
            'myaccount/form-login.php',
            'global/quantity-input.php',
            'archive-product.php',
            'single-product.php',
            'content-product.php'
        ]

        # Templates die oft Sicherheitsupdates haben
        self.security_sensitive_templates = [
            'checkout/form-checkout.php',
            'checkout/form-billing.php',
            'checkout/form-shipping.php',
            'checkout/payment.php',
            'myaccount/form-login.php',
            'myaccount/form-edit-account.php',
            'global/form-login.php'
        ]

    def analyze_critical_outdated_templates(self):
        """Analysiert die kritischen veralteten Templates im Detail"""
        print("=== DETAILANALYSE KRITISCHER VERALTETER TEMPLATES ===\n")

        # Liste der veralteten Templates aus der ersten Analyse
        outdated_templates = [
            ('order/attribution-details.php', '9.0.0', '9.5.0'),
            ('order/tracking.php', '2.2.0', '10.1.0'),
            ('order/order-details.php', '9.0.0', '10.1.0'),
            ('single-product/meta.php', '3.0.0', '9.7.0'),
            ('cart/mini-cart.php', '9.4.0', '10.0.0'),
            ('cart/cart.php', '7.9.0', '10.1.0'),
            ('myaccount/view-order.php', '3.0.0', '10.1.0'),
            ('myaccount/form-edit-account.php', '8.7.0', '9.7.0'),
            ('myaccount/orders.php', '9.2.0', '9.5.0'),
            ('loop/result-count.php', '9.4.0', '9.9.0'),
            ('loop/orderby.php', '3.6.0', '9.7.0'),
            ('checkout/form-login.php', '3.8.0', '10.0.0'),
            ('global/quantity-input.php', '9.4.0', '10.1.0'),
            ('single-product/add-to-cart/grouped.php', '7.0.1', '9.8.0')
        ]

        # Priorisiere Templates nach Wichtigkeit
        critical_outdated = []
        security_outdated = []
        major_version_gaps = []

        for template_path, theme_version, wc_version in outdated_templates:
            version_gap = self.calculate_version_gap(theme_version, wc_version)

            if template_path in self.critical_templates:
                critical_outdated.append((template_path, theme_version, wc_version, version_gap))

            if template_path in self.security_sensitive_templates:
                security_outdated.append((template_path, theme_version, wc_version, version_gap))

            if version_gap >= 2.0:  # Große Versionslücke
                major_version_gaps.append((template_path, theme_version, wc_version, version_gap))

        # Ausgabe der Ergebnisse
        print("🚨 KRITISCHE TEMPLATES (funktionell wichtig):")
        print("=" * 70)
        if critical_outdated:
            for template_path, theme_ver, wc_ver, gap in critical_outdated:
                priority = "🔴 HOCH" if gap >= 2.0 else "🟡 MITTEL"
                print(f"{priority} {template_path}")
                print(f"   Versionslücke: {theme_ver} → {wc_ver} (Gap: {gap:.1f})")
                self.analyze_template_changes(template_path)
                print()
        else:
            print("✅ Keine kritischen veralteten Templates\n")

        print("🔒 SICHERHEITSRELEVANTE TEMPLATES:")
        print("=" * 70)
        if security_outdated:
            for template_path, theme_ver, wc_ver, gap in security_outdated:
                priority = "🔴 HOCH" if gap >= 2.0 else "🟡 MITTEL"
                print(f"{priority} {template_path}")
                print(f"   Versionslücke: {theme_ver} → {wc_ver} (Gap: {gap:.1f})")
                print(f"   ⚠️  Mögliche Sicherheitsupdates!")
                self.analyze_template_changes(template_path)
                print()
        else:
            print("✅ Keine sicherheitsrelevanten veralteten Templates\n")

        print("📈 TEMPLATES MIT GROSSEN VERSIONSLÜCKEN (>2.0):")
        print("=" * 70)
        if major_version_gaps:
            for template_path, theme_ver, wc_ver, gap in major_version_gaps:
                print(f"🔴 {template_path}")
                print(f"   Versionslücke: {theme_ver} → {wc_ver} (Gap: {gap:.1f})")
                print(f"   🚨 Möglicherweise breaking changes!")
                self.analyze_template_changes(template_path)
                print()
        else:
            print("✅ Keine Templates mit großen Versionslücken\n")

    def calculate_version_gap(self, version1: str, version2: str) -> float:
        """Berechnet die Versionslücke zwischen zwei Versionen"""
        try:
            v1_parts = [int(x) for x in version1.split('.')]
            v2_parts = [int(x) for x in version2.split('.')]

            # Vereinfachte Berechnung: Hauptversion + Nebenversion/10
            v1_val = v1_parts[0] + (v1_parts[1] if len(v1_parts) > 1 else 0) / 10
            v2_val = v2_parts[0] + (v2_parts[1] if len(v2_parts) > 1 else 0) / 10

            return abs(v2_val - v1_val)
        except:
            return 0.0

    def analyze_template_changes(self, template_path: str):
        """Analysiert die Änderungen in einem Template"""
        theme_file = self.theme_path / "woocommerce" / template_path
        wc_file = self.woocommerce_path / "templates" / template_path

        if not theme_file.exists() or not wc_file.exists():
            print(f"   ❌ Template-Dateien nicht gefunden")
            return

        try:
            with open(theme_file, 'r', encoding='utf-8', errors='ignore') as f:
                theme_content = f.readlines()

            with open(wc_file, 'r', encoding='utf-8', errors='ignore') as f:
                wc_content = f.readlines()

            # Generiere Diff
            diff = list(difflib.unified_diff(
                theme_content, wc_content,
                fromfile=f'Theme: {template_path}',
                tofile=f'WooCommerce: {template_path}',
                n=3
            ))

            if len(diff) > 4:  # Hat Unterschiede
                changes_count = len([line for line in diff if line.startswith('+') or line.startswith('-')])
                print(f"   📝 {changes_count} Zeilen geändert")

                # Analysiere Art der Änderungen
                significant_changes = self.analyze_diff_significance(diff)
                for change_type, count in significant_changes.items():
                    if count > 0:
                        print(f"   - {change_type}: {count}")

                # Prüfe auf kritische Änderungen
                critical_indicators = [
                    'security', 'sanitize', 'escape', 'nonce', 'csrf',
                    'sql', 'query', 'vulnerability', 'fix', 'patch'
                ]

                diff_text = ''.join(diff).lower()
                found_critical = [indicator for indicator in critical_indicators if indicator in diff_text]
                if found_critical:
                    print(f"   🚨 Mögliche kritische Änderungen: {', '.join(found_critical)}")
            else:
                print(f"   ✅ Nur minimale Änderungen")

        except Exception as e:
            print(f"   ❌ Fehler beim Analysieren: {e}")

    def analyze_diff_significance(self, diff_lines: List[str]) -> Dict[str, int]:
        """Analysiert die Bedeutung der Änderungen"""
        changes = {
            'HTML-Struktur': 0,
            'PHP-Logik': 0,
            'CSS-Klassen': 0,
            'Hooks/Actions': 0,
            'Sicherheit': 0,
            'JavaScript': 0
        }

        for line in diff_lines:
            if line.startswith('+') or line.startswith('-'):
                line_lower = line.lower()

                # HTML-Struktur
                if any(tag in line_lower for tag in ['<div', '<span', '<p>', '<ul', '<li', '<form']):
                    changes['HTML-Struktur'] += 1

                # PHP-Logik
                if any(php in line_lower for php in ['if ', 'else', 'foreach', 'while', 'function', '$']):
                    changes['PHP-Logik'] += 1

                # CSS-Klassen
                if 'class=' in line_lower or 'id=' in line_lower:
                    changes['CSS-Klassen'] += 1

                # WordPress Hooks
                if any(hook in line_lower for hook in ['do_action', 'apply_filters', 'add_action', 'add_filter']):
                    changes['Hooks/Actions'] += 1

                # Sicherheit
                if any(sec in line_lower for sec in ['esc_', 'wp_nonce', 'sanitize', 'wp_verify_nonce']):
                    changes['Sicherheit'] += 1

                # JavaScript
                if any(js in line_lower for js in ['<script', 'javascript', 'jquery', '$(', '.js']):
                    changes['JavaScript'] += 1

        return changes

    def recommend_update_priority(self):
        """Empfiehlt Prioritäten für Template-Updates"""
        print("\n🎯 UPDATE-PRIORITÄTEN EMPFEHLUNG:")
        print("=" * 70)

        # Kritische Updates (sofort)
        critical_updates = [
            'cart/cart.php',
            'cart/mini-cart.php',
            'checkout/form-login.php',
            'myaccount/form-edit-account.php',
            'global/quantity-input.php'
        ]

        print("🔴 SOFORT (Kritisch für Funktion/Sicherheit):")
        for template in critical_updates:
            print(f"   • {template}")

        print("\n🟡 BALD (Wichtige Verbesserungen):")
        medium_updates = [
            'order/tracking.php',
            'order/order-details.php',
            'single-product/meta.php',
            'loop/orderby.php',
            'single-product/add-to-cart/grouped.php'
        ]
        for template in medium_updates:
            print(f"   • {template}")

        print("\n🟢 GEPLANT (Bei Gelegenheit):")
        low_updates = [
            'myaccount/view-order.php',
            'myaccount/orders.php',
            'loop/result-count.php',
            'order/attribution-details.php'
        ]
        for template in low_updates:
            print(f"   • {template}")

        print("\n💡 EMPFOHLENES VORGEHEN:")
        print("=" * 70)
        print("1. 🔄 Backup erstellen vor allen Änderungen")
        print("2. 🧪 Staging-Umgebung für Tests nutzen")
        print("3. 📝 Eigene Anpassungen dokumentieren")
        print("4. 🔍 Template für Template updaten und testen")
        print("5. 🎨 Design-Anpassungen nach Update erneut anwenden")
        print("6. ✅ Funktionalität auf Live-System testen")

def main():
    theme_path = "/home/imabidin/badspiegel/wordpress/wp-content/themes/bsawesome"
    woocommerce_path = "/home/imabidin/badspiegel/wordpress/wp-content/plugins/woocommerce"

    analyzer = DetailedTemplateAnalyzer(theme_path, woocommerce_path)
    analyzer.analyze_critical_outdated_templates()
    analyzer.recommend_update_priority()

if __name__ == "__main__":
    main()
