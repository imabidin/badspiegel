import tkinter as tk
from tkinter import ttk, scrolledtext, messagebox
import threading
import os
import sys
from datetime import datetime
import csv
import pandas as pd
import re
import logging

class MatrixToolGUI:
    def __init__(self, root):
        self.root = root
        self.root.title("Matrix Tools - Quickstart")
        self.root.geometry("800x600")
        
        # Create main frame
        main_frame = ttk.Frame(root, padding="10")
        main_frame.grid(row=0, column=0, sticky=(tk.W, tk.E, tk.N, tk.S))
        
        # Configure grid weights
        root.columnconfigure(0, weight=1)
        root.rowconfigure(0, weight=1)
        main_frame.columnconfigure(0, weight=1)
        main_frame.rowconfigure(3, weight=1)
        
        # Title
        title_label = ttk.Label(main_frame, text="Matrix Tools", font=("Arial", 16, "bold"))
        title_label.grid(row=0, column=0, pady=(0, 20))
        
        # Buttons frame
        buttons_frame = ttk.Frame(main_frame)
        buttons_frame.grid(row=1, column=0, sticky=(tk.W, tk.E), pady=(0, 10))
        buttons_frame.columnconfigure(0, weight=1)
        buttons_frame.columnconfigure(1, weight=1)
        buttons_frame.columnconfigure(2, weight=1)
        
        # Function buttons
        self.btn_analyze = ttk.Button(buttons_frame, text="1. Matrizen Analysieren", 
                                     command=self.run_analyze_matrices, width=25)
        self.btn_analyze.grid(row=0, column=0, padx=5, pady=5, sticky=(tk.W, tk.E))
        
        self.btn_extract = ttk.Button(buttons_frame, text="2. Basispreise Extrahieren", 
                                     command=self.run_extract_prices, width=25)
        self.btn_extract.grid(row=0, column=1, padx=5, pady=5, sticky=(tk.W, tk.E))
        
        self.btn_compare = ttk.Button(buttons_frame, text="3. Matrizen Vergleichen", 
                                     command=self.run_compare_matrices, width=25)
        self.btn_compare.grid(row=0, column=2, padx=5, pady=5, sticky=(tk.W, tk.E))
        
        # Progress bar
        self.progress = ttk.Progressbar(main_frame, mode='indeterminate')
        self.progress.grid(row=2, column=0, sticky=(tk.W, tk.E), pady=(0, 10))
        
        # Output text area
        self.output_text = scrolledtext.ScrolledText(main_frame, height=25, width=90)
        self.output_text.grid(row=3, column=0, sticky=(tk.W, tk.E, tk.N, tk.S))
        
        # Status bar
        self.status_var = tk.StringVar()
        self.status_var.set("Bereit")
        status_bar = ttk.Label(main_frame, textvariable=self.status_var, relief=tk.SUNKEN)
        status_bar.grid(row=4, column=0, sticky=(tk.W, tk.E), pady=(10, 0))
        
        self.script_dir = os.path.dirname(os.path.abspath(__file__))

    def log_output(self, message):
        """Add message to output text area"""
        self.output_text.insert(tk.END, f"[{datetime.now().strftime('%H:%M:%S')}] {message}\n")
        self.output_text.see(tk.END)
        self.root.update_idletasks()

    def set_buttons_state(self, state):
        """Enable or disable all buttons"""
        self.btn_analyze.config(state=state)
        self.btn_extract.config(state=state)
        self.btn_compare.config(state=state)

    def run_analyze_matrices(self):
        """Run matrix analysis in separate thread"""
        def worker():
            try:
                self.set_buttons_state('disabled')
                self.progress.start()
                self.status_var.set("Analysiere Matrizen...")
                
                from analyze_matrices import analyze_csv_matrix
                
                matrix_dir = os.path.join(self.script_dir, 'csv')
                theme_root_dir = os.path.abspath(os.path.join(self.script_dir, '..', '..', '..'))
                output_file = os.path.join(theme_root_dir, 'matrix_analysis.xlsx')
                
                self.log_output("=== MATRIX ANALYSE GESTARTET ===")
                self.log_output(f"Suche in Verzeichnis: {matrix_dir}")
                
                analysis_results = []
                
                if not os.path.isdir(matrix_dir):
                    self.log_output(f"❌ Verzeichnis nicht gefunden: {matrix_dir}")
                    return
                
                csv_files = [f for f in os.listdir(matrix_dir) if f.endswith('.csv')]
                self.log_output(f"Gefundene CSV-Dateien: {len(csv_files)}")
                
                for filename in csv_files:
                    file_path = os.path.join(matrix_dir, filename)
                    result = analyze_csv_matrix(file_path)
                    if result:
                        num_widths, num_heights = result
                        analysis_results.append({
                            'Dateiname': filename,
                            'Anzahl Breiten (Spalten)': num_widths,
                            'Anzahl Höhen (Zeilen)': num_heights
                        })
                        self.log_output(f"✅ {filename}: {num_widths} Breiten, {num_heights} Höhen")
                    else:
                        self.log_output(f"❌ Fehler bei {filename}")
                
                if analysis_results:
                    df = pd.DataFrame(analysis_results)
                    df.to_excel(output_file, index=False)
                    self.log_output(f"✅ Analyse abgeschlossen. Ergebnisse gespeichert: {output_file}")
                    self.log_output(f"📊 {len(analysis_results)} Matrizen erfolgreich analysiert")
                else:
                    self.log_output("❌ Keine gültigen CSV-Matrix-Dateien gefunden")
                
            except Exception as e:
                self.log_output(f"❌ Fehler bei Matrix-Analyse: {str(e)}")
            finally:
                self.progress.stop()
                self.set_buttons_state('normal')
                self.status_var.set("Bereit")
        
        threading.Thread(target=worker, daemon=True).start()

    def run_extract_prices(self):
        """Run price extraction in separate thread"""
        def worker():
            try:
                self.set_buttons_state('disabled')
                self.progress.start()
                self.status_var.set("Extrahiere Basispreise...")
                
                # Import and run extract_base_prices functionality
                sys.path.insert(0, self.script_dir)
                from extract_base_prices import read_produktpalette, process_products, save_results, generate_summary
                
                self.log_output("=== BASISPREIS EXTRAKTION GESTARTET ===")
                
                # Read product palette
                self.log_output("📖 Lade Produktpalette...")
                df_products = read_produktpalette()
                if df_products is None:
                    self.log_output("❌ Fehler beim Laden der Produktpalette")
                    return
                
                self.log_output(f"✅ {len(df_products)} Produkte geladen")
                
                # Process products
                self.log_output("🔍 Extrahiere Basispreise...")
                results = process_products(df_products)
                
                # Save results
                self.log_output("💾 Speichere Ergebnisse...")
                if save_results(results):
                    self.log_output("✅ Excel-Datei erstellt")
                    
                    # Summary
                    total = len(results)
                    successful = len([r for r in results if r['Basispreis_90er'] is not None])
                    failed = total - successful
                    
                    self.log_output(f"📊 ZUSAMMENFASSUNG:")
                    self.log_output(f"   Gesamte Produkte: {total}")
                    self.log_output(f"   Erfolgreich: {successful}")
                    self.log_output(f"   Fehlgeschlagen: {failed}")
                    
                    if failed > 0:
                        self.log_output("❌ Fehlgeschlagene Produkte:")
                        for result in results:
                            if result['Basispreis_90er'] is None:
                                self.log_output(f"   - {result['Name']}: {result['Status']}")
                else:
                    self.log_output("❌ Fehler beim Speichern")
                
            except Exception as e:
                self.log_output(f"❌ Fehler bei Basispreis-Extraktion: {str(e)}")
            finally:
                self.progress.stop()
                self.set_buttons_state('normal')
                self.status_var.set("Bereit")
        
        threading.Thread(target=worker, daemon=True).start()

    def run_compare_matrices(self):
        """Compare old matrices with new matrices"""
        def worker():
            try:
                self.set_buttons_state('disabled')
                self.progress.start()
                self.status_var.set("Vergleiche Matrizen...")
                
                self.log_output("=== MATRIX VERGLEICH GESTARTET ===")
                
                old_dir = os.path.join(self.script_dir, 'csv')
                new_dir = os.path.join(self.script_dir, 'csv', 'new')
                output_file = os.path.join(self.script_dir, f'matrix_comparison_{datetime.now().strftime("%Y%m%d_%H%M%S")}.txt')
                
                self.log_output(f"Alte Matrizen: {old_dir}")
                self.log_output(f"Neue Matrizen: {new_dir}")
                
                if not os.path.exists(old_dir):
                    self.log_output(f"❌ Verzeichnis 'csv' nicht gefunden: {old_dir}")
                    return
                
                if not os.path.exists(new_dir):
                    self.log_output(f"❌ Verzeichnis 'csv/new' nicht gefunden: {new_dir}")
                    return
                
                # Get file lists
                old_files = set(f for f in os.listdir(old_dir) if f.endswith('.csv'))
                new_files = set(f for f in os.listdir(new_dir) if f.endswith('.csv'))
                
                self.log_output(f"Alte CSV-Dateien: {len(old_files)}")
                self.log_output(f"Neue CSV-Dateien: {len(new_files)}")
                
                # Compare files
                comparison_results = []
                comparison_results.append("MATRIX VERGLEICH BERICHT")
                comparison_results.append("=" * 50)
                comparison_results.append(f"Datum: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
                comparison_results.append("")
                
                # Files only in old
                only_in_old = old_files - new_files
                if only_in_old:
                    comparison_results.append("DATEIEN NUR IN ALT:")
                    for file in sorted(only_in_old):
                        comparison_results.append(f"  - {file}")
                        self.log_output(f"📤 Nur in alt: {file}")
                    comparison_results.append("")
                
                # Files only in new
                only_in_new = new_files - old_files
                if only_in_new:
                    comparison_results.append("DATEIEN NUR IN NEU:")
                    for file in sorted(only_in_new):
                        comparison_results.append(f"  - {file}")
                        self.log_output(f"📥 Nur in neu: {file}")
                    comparison_results.append("")
                
                # Files in both - compare dimensions and prices
                common_files = old_files & new_files
                self.log_output(f"🔍 Vergleiche {len(common_files)} gemeinsame Dateien...")
                
                dimension_changes = []
                price_changes = []
                
                for filename in sorted(common_files):
                    old_file = os.path.join(old_dir, filename)
                    new_file = os.path.join(new_dir, filename)
                    
                    # Compare dimensions
                    old_result = self.analyze_csv_matrix(old_file)
                    new_result = self.analyze_csv_matrix(new_file)
                    
                    if old_result and new_result:
                        old_w, old_h = old_result
                        new_w, new_h = new_result
                        
                        if old_w != new_w or old_h != new_h:
                            change = f"{filename}: {old_w}x{old_h} -> {new_w}x{new_h}"
                            dimension_changes.append(change)
                            self.log_output(f"📏 Dimensionen geändert: {change}")
                    
                    # Compare prices
                    price_diff = self.compare_csv_prices(old_file, new_file)
                    if price_diff:
                        price_changes.extend([f"{filename}:"] + price_diff + [""])
                        self.log_output(f"💰 Preisänderungen in: {filename}")
                
                # Add dimension changes to report
                if dimension_changes:
                    comparison_results.append("DIMENSIONSÄNDERUNGEN:")
                    comparison_results.extend([f"  - {change}" for change in dimension_changes])
                    comparison_results.append("")
                
                # Add price changes to report
                if price_changes:
                    comparison_results.append("PREISÄNDERUNGEN:")
                    comparison_results.extend([f"  {line}" for line in price_changes])
                
                # Summary
                comparison_results.append("ZUSAMMENFASSUNG:")
                comparison_results.append(f"  - Nur in alt: {len(only_in_old)}")
                comparison_results.append(f"  - Nur in neu: {len(only_in_new)}")
                comparison_results.append(f"  - Gemeinsame Dateien: {len(common_files)}")
                comparison_results.append(f"  - Dimensionsänderungen: {len(dimension_changes)}")
                comparison_results.append(f"  - Dateien mit Preisänderungen: {len([f for f in price_changes if f.endswith(':')])}")
                
                # Save report
                with open(output_file, 'w', encoding='utf-8') as f:
                    f.write('\n'.join(comparison_results))
                
                self.log_output(f"✅ Vergleichsbericht gespeichert: {output_file}")
                self.log_output("📊 ZUSAMMENFASSUNG:")
                self.log_output(f"   Nur in alt: {len(only_in_old)}")
                self.log_output(f"   Nur in neu: {len(only_in_new)}")
                self.log_output(f"   Dimensionsänderungen: {len(dimension_changes)}")
                
            except Exception as e:
                self.log_output(f"❌ Fehler bei Matrix-Vergleich: {str(e)}")
            finally:
                self.progress.stop()
                self.set_buttons_state('normal')
                self.status_var.set("Bereit")
        
        threading.Thread(target=worker, daemon=True).start()

    def analyze_csv_matrix(self, file_path):
        """Analyze CSV matrix dimensions (from analyze_matrices.py)"""
        try:
            with open(file_path, 'r', encoding='utf-8') as f:
                reader = csv.reader(f, delimiter=';')
                rows = list(reader)
            
            if not rows or not rows[0]:
                return None
            
            num_heights = len(rows) - 1
            num_widths = len(rows[0]) - 1
            
            return num_widths, num_heights
        except Exception:
            return None

    def compare_csv_prices(self, old_file, new_file):
        """Compare prices between two CSV files"""
        try:
            # Read old file
            old_data = {}
            with open(old_file, 'r', encoding='utf-8') as f:
                reader = csv.reader(f, delimiter=';')
                rows = list(reader)
                if rows:
                    heights = [row[0] for row in rows[1:] if row]
                    widths = rows[0][1:] if len(rows[0]) > 1 else []
                    
                    for i, height in enumerate(heights, 1):
                        if i < len(rows) and len(rows[i]) > 1:
                            for j, width in enumerate(widths, 1):
                                if j < len(rows[i]):
                                    try:
                                        price = float(rows[i][j].replace(',', '.'))
                                        old_data[f"{width}x{height}"] = price
                                    except:
                                        pass
            
            # Read new file
            new_data = {}
            with open(new_file, 'r', encoding='utf-8') as f:
                reader = csv.reader(f, delimiter=';')
                rows = list(reader)
                if rows:
                    heights = [row[0] for row in rows[1:] if row]
                    widths = rows[0][1:] if len(rows[0]) > 1 else []
                    
                    for i, height in enumerate(heights, 1):
                        if i < len(rows) and len(rows[i]) > 1:
                            for j, width in enumerate(widths, 1):
                                if j < len(rows[i]):
                                    try:
                                        price = float(rows[i][j].replace(',', '.'))
                                        new_data[f"{width}x{height}"] = price
                                    except:
                                        pass
            
            # Compare prices
            changes = []
            all_sizes = set(old_data.keys()) | set(new_data.keys())
            
            for size in sorted(all_sizes):
                old_price = old_data.get(size)
                new_price = new_data.get(size)
                
                if old_price is None and new_price is not None:
                    changes.append(f"    NEU: {size} = {new_price}")
                elif old_price is not None and new_price is None:
                    changes.append(f"    ENTFERNT: {size} = {old_price}")
                elif old_price != new_price:
                    changes.append(f"    GEÄNDERT: {size}: {old_price} -> {new_price}")
            
            return changes
            
        except Exception as e:
            return [f"    FEHLER beim Vergleich: {str(e)}"]

def main():
    root = tk.Tk()
    app = MatrixToolGUI(root)
    root.mainloop()

if __name__ == "__main__":
    main()
