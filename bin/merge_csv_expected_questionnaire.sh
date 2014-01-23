#!/usr/bin/env bash

{ tail -q -n +1 *Water.csv ; } > _WATER_RAW.csv
{ tail -q -n +1 *Sanitation.csv ; } > _SANITATION_RAW.csv

# Reorder columns to match format of GIMS output
awk -F "\",\"" '{print $1 "\",\"" $2 "\",\"" $3 "\",\"" $4 "\",\"" $7 "\",\"" $5 "\",\"" $9 "\",\"\",\"\",\"" $13 "\",\"" $11 "\",\"" $15 "\",\"\",\"\",\"" $19 "\",\"" $17 "\",\"" $21 "\",\"\",\"\"" }' _WATER_RAW.csv > _WATER_EXPECTED.csv
awk -F "\",\"" '{print $1 "\",\"" $2 "\",\"" $3 "\",\"" $4 "\",\"" $7 "\",\"" $5 "\",\"\",\"" $9 "\",\"\",\"" $11 "\",\"" $15 "\",\"" $13 "\",\"\",\"" $17 "\",\"\",\"" $19 "\",\"" $23 "\",\"" $21 "\",\"\",\"" $25 "\",\"\",\"" $27 "\"" }' _SANITATION_RAW.csv > _SANITATION_EXPECTED.csv

rm _WATER_RAW.csv _SANITATION_RAW.csv