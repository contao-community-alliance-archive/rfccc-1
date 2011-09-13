#!/bin/bash

# require 2 parameters
# parameter 1, target directory
DIR="$1"
# parameter 2, owner/group
OWNER="$2"

# create temporary directory
TMP="/tmp/contao-er3-$RANDOM"
mkdir "$TMP"

# clone the project
git clone https://trilin@github.com/Discordier/Contao-ER3.git "$TMP"

# enter the rfccc-1 document
cd "$TMP/doc/rfccc-1"

# generate index
pdflatex rfccc-1.tex

# generate final pdf
pdflatex rfccc-1.tex

# get date string
DATE=$(date "+%F")

# change owner
chown "$OWNER" "rfccc-1.pdf"

# move to new location
mv "rfccc-1.pdf" "$DIR/rfccc-1-nightly-$DATE.pdf"

# cleanup
cd "/"
rm -rf "$TMP"
