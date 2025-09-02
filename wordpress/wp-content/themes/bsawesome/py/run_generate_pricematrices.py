import importlib.util
import os

# Pfad zu generate_pricematrices.py
script_dir = os.path.dirname(os.path.abspath(__file__))
configurator_dir = os.path.join(script_dir, '..', 'inc', 'configurator')
pricematrices_path = os.path.join(configurator_dir, 'generate_pricematrices.py')

# Dynamisches Laden des Moduls
try:
    spec_pricematrices = importlib.util.spec_from_file_location("generate_pricematrices", pricematrices_path)
    generate_pricematrices = importlib.util.module_from_spec(spec_pricematrices)
    spec_pricematrices.loader.exec_module(generate_pricematrices)
except Exception as e:
    print(f"Fehler beim Laden von generate_pricematrices.py: {e}")

# Hauptfunktion ausführen
if __name__ == "__main__":
    print("Starte generate_pricematrices.main()...")
    try:
        generate_pricematrices.main()
    except AttributeError:
        print("Die Funktion 'main' existiert nicht in generate_pricematrices.py")
    except Exception as e:
        print(f"Fehler beim Ausführen von generate_pricematrices.main(): {e}")
