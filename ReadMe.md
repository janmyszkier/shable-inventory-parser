## Shable Inventory to JSON
Small tool to validate https://github.com/verknowsys/Shable `inventory` files. 
made to convert inventory to JSON format for easier read.

### How to use
1. Clone this repo

2. copy the `inventory` file from Shable to root folder

3, Run 
```
php validateAndConvert.php
```
if all is good, the JSON output is presented. If something is wrong, it will print first error and `die()`

