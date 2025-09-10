import importlib.util
import sys
import os

# Pfad zu den Modulen hinzufügen
script_dir = os.path.dirname(os.path.abspath(__file__))
configurator_dir = os.path.join(script_dir, '..', 'inc', 'configurator')

# Dynamisches Laden der Module
try:
    spec_options = importlib.util.spec_from_file_location("generate_options", os.path.join(configurator_dir, "generate_options.py"))
    generate_options = importlib.util.module_from_spec(spec_options)
    spec_options.loader.exec_module(generate_options)

    spec_pricematrices = importlib.util.spec_from_file_location("generate_pricematrices", os.path.join(configurator_dir, "generate_pricematrices.py"))
    generate_pricematrices = importlib.util.module_from_spec(spec_pricematrices)
    spec_pricematrices.loader.exec_module(generate_pricematrices)
except Exception as e:
    print(f"Fehler beim dynamischen Laden der Module: {e}")

# Beispielaufruf der Hauptfunktionen, falls vorhanden
if __name__ == "__main__":
    print("Starte generate_options.main()...")
    try:
        generate_options.main()
    except AttributeError:
        print("Die Funktion 'main' existiert nicht in generate_options.py")
    except Exception as e:
        print(f"Fehler beim Ausführen von generate_options.main(): {e}")

    print("Starte generate_pricematrices.main()...")
    try:
        generate_pricematrices.main()
    except AttributeError:
        print("Die Funktion 'main' existiert nicht in generate_pricematrices.py")
    except Exception as e:
        print(f"Fehler beim Ausführen von generate_pricematrices.main(): {e}")
