#!/bin/bash

# require some parameters
# parameter 1, target directory
TARGET_DIR="$1"
# parameter 2, owner/group
OWNER="$2"
# parameter 3, url prefix
URL="$3"
# parameter 4, local twitter user (twidge have to be configured for this account)
TWITTER_USER="$4"

# the local repository
CACHE="/var/cache/contao-er3-nigthly"
TEXFILE="rfccc-1.tex"
TEXFILEDIR="/doc/rfccc-1"

# clone or update the repository
if [[ ! -d "$CACHE" ]]; then
	# create git directory
	mkdir -p "$CACHE"

	# clone the project
	git clone https://trilin@github.com/Discordier/Contao-ER3.git "$CACHE/git"
	
	# enter repository
	cd "$CACHE"
	
	# set upstream (this enables pull support)
	git branch --set-upstream master origin/master
else
	# enter repository
	cd "$CACHE"
	
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

	# generate index
	pdflatex -interaction=nonstopmode $TEXFILE
	# generate diagrams
	mpost --interaction nonstopmode $MPOSTFILE
	# generate final pdf
	pdflatex -interaction=nonstopmode  $TEXFILE

	# change owner
	chown "$OWNER" $PDFFILE

	# move to new location
	mv $PDFFILE "$TARGET_DIR/$PDFFILENIGHTLY"

	# tweet
	if [[ -n "$URL" && -n "$TWITTER_USER" ]]; then
		sudo -u "$TWITTER_USER" -H twidge update "New nightly build of Contao ER3 documentation is available. $URL/$PDFFILENIGHTLY"
	fi

	# save the current commit
	echo -n "$CURRENT" > "$CACHE/nightly-commit"
fi
