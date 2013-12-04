#!/usr/bin/env bash

{ tail -q -n +1  *Water.csv ; } > _ALL_WATER.csv
{ tail -q -n +1  *Sanitation.csv ; } > _ALL_SANITATION.csv

# Reorder columns to match format of GIMS output
awk -F "," '{print $1 "," $2 "," $3 "," $4 "," $7 "," $5 "," $9 ",\"\",\"\"," $13 "," $11 "," $15 ",\"\",\"\"," $19 "," $17 "," $21 ",\"\",\"\"" }' _ALL_WATER.csv > _ALL_WATER_REORDERED.csv
awk -F "," '{print $1 "," $2 "," $3 "," $4 "," $7 "," $5 ",\"\"," $9 ",\"\"," $11 "," $15 "," $13 ",\"\"," $17 ",\"\"," $19 "," $23 "," $21 ",\"\"," $25 ",\"\"," $27 }' _ALL_SANITATION.csv > _ALL_SANITATION_REORDERED.csv