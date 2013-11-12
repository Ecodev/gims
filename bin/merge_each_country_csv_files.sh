#!/usr/bin/env bash

{ head -q -n 1 *Uganda\ -\ Water.csv && tail -q -n +2  *Water.csv ; } > _ALL_WATER.csv
{ head -q -n 1 *Uganda\ -\ Sanitation.csv && tail -q -n +2  *Sanitation.csv ; } > _ALL_SANITATION.csv
