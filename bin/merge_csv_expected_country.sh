#!/usr/bin/env bash

{ tail -q -n +1 *Water.csv ; } > _WATER_RAW.csv
{ tail -q -n +1 *Sanitation.csv ; } > _SANITATION_RAW.csv

# Reorder columns to match format of GIMS output
awk -F "," '{print $1 "," $2 "," $3 ",\"\",\"\",\"\"," $6 ",\"\"," $4 ",\"\"," $8 ",\"\"," $5 ",\"\"," $7 ",\"\"," $11 ",\"\"," $9 ",\"\"," $13 ",\"\"," $10 ",\"\"," $12 ",\"\"," $16 ",\"\"," $14 ",\"\"," $18 ",\"\"," $15 ",\"\"," $17 }' _WATER_RAW.csv > _WATER_EXPECTED.csv
awk -F "," '{print $1 "," $2 "," $3 ",\"\",\"\",\"\"," $4 ",\"\",\"\",\"\"," $5 ",\"\"," $6 ",\"\"," $7 ",\"\"," $8 ",\"\"," $9 ",\"\",\"\",\"\"," $10 ",\"\"," $11 ",\"\"," $12 ",\"\"," $13 ",\"\"," $14 ",\"\",\"\",\"\"," $15 ",\"\"," $16 ",\"\"," $17 ",\"\"," $18 }' _SANITATION_RAW.csv > _SANITATION_EXPECTED.csv

rm _WATER_RAW.csv _SANITATION_RAW.csv