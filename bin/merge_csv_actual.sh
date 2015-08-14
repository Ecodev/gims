#!/usr/bin/env bash

{ tail -q -n +4  *Water.csv ; } | grep  -v 'GLAAS-2013' > _WATER_ACTUAL.csv
{ tail -q -n +4  *Sanitation.csv ; } | grep  -v 'GLAAS-2013' > _SANITATION_ACTUAL.csv
