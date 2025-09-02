import os
import re
import csv
import pandas as pd

def analyze_csv_matrix(file_path):
    """
    Analyzes a CSV price matrix file to find the number of widths and heights.
    Assumes first row are width headers and first column are height labels.
    """
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            # Assuming semicolon delimiter, common for German CSVs
            reader = csv.reader(f, delimiter=';')
            rows = list(reader)
        
        if not rows or not rows[0]:
            return 0, 0

        # Number of heights = number of rows - 1 (for header row)
        num_heights = len(rows) - 1
        
        # Number of widths = number of columns - 1 (for label column)
        num_widths = len(rows[0]) - 1
        
        return num_widths, num_heights
    except Exception as e:
        print(f"Could not process file {file_path}: {e}")
        return None

def main():
    """
    Main function to scan directory and write analysis to a file.
    """
    script_dir = os.path.dirname(__file__)
    # The 'csv' directory is a sibling to this script
    matrix_dir = os.path.join(script_dir, 'csv')
    # Output file should be in the theme root, which is 3 levels up
    theme_root_dir = os.path.abspath(os.path.join(script_dir, '..', '..', '..'))
    output_file = os.path.join(theme_root_dir, 'matrix_analysis.xlsx')
    
    analysis_results = []
    
    if not os.path.isdir(matrix_dir):
        print(f"Directory not found: {matrix_dir}")
        return

    for filename in os.listdir(matrix_dir):
        if filename.endswith('.csv'):
            file_path = os.path.join(matrix_dir, filename)
            result = analyze_csv_matrix(file_path)
            if result:
                num_widths, num_heights = result
                analysis_results.append({
                    'Dateiname': filename,
                    'Anzahl Breiten (Spalten)': num_widths,
                    'Anzahl Höhen (Zeilen)': num_heights
                })

    if analysis_results:
        # Create a pandas DataFrame from the results
        df = pd.DataFrame(analysis_results)
        
        # Write the DataFrame to an Excel file
        df.to_excel(output_file, index=False)
        
        print(f"Analyse abgeschlossen. Ergebnisse in {output_file} gespeichert.")
    else:
        print("Keine gültigen CSV-Matrix-Dateien zur Analyse gefunden.")

if __name__ == '__main__':
    main()
