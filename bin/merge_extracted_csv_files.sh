#!/usr/bin/env bash

{ tail -q -n +1  *Water.csv ; } > _ALL_WATER.csv
{ tail -q -n +1  *Sanitation.csv ; } > _ALL_SANITATION.csv
