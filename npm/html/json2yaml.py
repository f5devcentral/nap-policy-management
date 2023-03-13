import yaml
import json
import sys


input_file = sys.argv[1]
if 'json' in input_file:
    mod_file = input_file[:-5] + ".yaml"
else:
    print("failed matching the extension")
    exit()


with open(input_file, 'r') as file:
   try:
      configuration = json.load(file)
      with open(mod_file, 'w') as json_file:
         yaml.dump(configuration, json_file, indent=2)

      print("success")
   except:
      print("parsing error") 

    
   
