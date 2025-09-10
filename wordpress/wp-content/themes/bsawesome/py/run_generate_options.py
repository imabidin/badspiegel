import importlib.util
import os

# Pfad zu generate_options.py
script_dir = os.path.dirname(os.path.abspath(__file__))
configurator_dir = os.path.join(script_dir, '..', 'inc', 'configurator')
options_path = os.path.join(configurator_dir, 'generate_options.py')

# Dynamisches Laden des Moduls
try:
    spec_options = importlib.util.spec_from_file_location("generate_options", options_path)
    generate_options = importlib.util.module_from_spec(spec_options)
    spec_options.loader.exec_module(generate_options)
except Exception as e:
    print(f"Fehler beim Laden von generate_options.py: {e}")

# Hauptfunktion ausführen
if __name__ == "__main__":
    print("Starte generate_options.main()...")
    try:
        generate_options.main()
    except AttributeError:
        print("Die Funktion 'main' existiert nicht in generate_options.py")
    except Exception as e:
        print(f"Fehler beim Ausführen von generate_options.main(): {e}")
