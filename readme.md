This program determines a DXCC from it's callsign with the help of the country files from AD1C.
Get the Big CTY file from here: https://www.country-files.com/category/big-cty/

Ported version from PERL to PHP. Based on the works by Fabian Kurz, DJ5CW: http://fkurz.net/ham/dxcc.html
Source here: https://git.fkurz.net/dj1yfk/dxcc

The following changes has been made to get it to work in PHP:

    1. Line 267-268 The swapping of $a and $b is not done
    
    2. Needs special handling of KG4 Guantanamo Bay
    
    3. Regex fix: $lidadditions = '/^QRP\$|^LGT\$/';
    
    4. Regex fix: $csadditions = "/(^P\$)|(^M{1,2}\$)|(^AM\$)|(^A\$)/";
    
    5. Regex fix: line 527: /\s+([*A-Za-z0-9\/]+):\s+$/ -> /\s+([*A-Za-z0-9\/]+):+$/
