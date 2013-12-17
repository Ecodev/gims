#!/usr/bin/env bash

{ tail -q -n +2  *Water.csv ; } > _WATER_ACTUAL.csv
{ tail -q -n +2  *Sanitation.csv ; } > _SANITATION_ACTUAL.csv
