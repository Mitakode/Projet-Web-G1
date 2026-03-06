#!/bin/bash
set -e

echo "Serveur web prêt ! Apache tourne."
exec apache2ctl -D FOREGROUND
