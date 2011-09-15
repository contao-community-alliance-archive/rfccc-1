#!/bin/bash

# require some parameters
# parameter 1, target directory
TARGET_DIR="$1"
# parameter 2, url prefix
URL="$3"

# the local repository
CACHE="$HOME/.contao-er3-nigthly"
TEXFILE="rfccc-1.tex"
TEXFILEDIR="/doc/rfccc-1"

# clone or update the repository
if [[ ! -d "$CACHE" ]]; then
	# create git directory
	mkdir -p "$CACHE"

	# clone the project
	git clone https://github.com/Discordier/Contao-ER3.git "$CACHE/git"
	
	# enter repository
	cd "$CACHE/git"
	
	# set upstream (this enables pull support)
	git branch --set-upstream master origin/master
else
	# enter repository
	cd "$CACHE/git"
	
	# update the project
	git pull -q --rebase -f
fi

# enter the rfccc-1 document
cd "$CACHE/git/$TEXFILEDIR"

# find previous commit
if [[ -f "$CACHE/nightly-commit" ]]; then
	PREVIOUS=$(cat "$CACHE/nightly-commit")
else
	PREVIOUS=""
fi

# find current commit
CURRENT=$(git log -1 | head -1)

# generate nightly build if there is a new commit
if [[ "$PREVIOUS" != "$CURRENT" ]]; then
	TEXFILE="rfccc-1.tex"
	PDFFILE="`basename $TEXFILE .tex`.pdf"
	PDFFILENIGHTLY="`basename $TEXFILE .tex`-nightly.pdf"
	MPOSTFILE="`basename $TEXFILE .tex`.mp"
	
	if [[ ! -w "$TARGET_DIR/$PDFFILENIGHTLY" ]]; then
		echo "I need to have write permissions on \"$TARGET_DIR/$PDFFILENIGHTLY\"!"
		exit 1
	fi

	# generate index
	pdflatex -q -interaction=nonstopmode $TEXFILE
	# generate diagrams
	mpost $MPOSTFILE
	# generate final pdf
	pdflatex -q -interaction=nonstopmode  $TEXFILE

	# copy to new location
	#   do not move, this will change owner
	cp $PDFFILE "$TARGET_DIR/$PDFFILENIGHTLY"

	# tweet
	if [[ -n "$URL" ]]; then
		twidge update "New nightly build of Contao ER3 documentation is available. $URL/$PDFFILENIGHTLY"
	fi

	# save the current commit
	echo -n "$CURRENT" > "$CACHE/nightly-commit"
fi

exit 0