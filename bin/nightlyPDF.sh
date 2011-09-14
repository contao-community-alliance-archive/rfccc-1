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
cd "$CACHE/git/doc/rfccc-1"

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
	# generate index
	pdflatex rfccc-1.tex

	# generate final pdf
	pdflatex rfccc-1.tex

	# change owner
	chown "$OWNER" "rfccc-1.pdf"

	# move to new location
	mv "rfccc-1.pdf" "$TARGET_DIR/rfccc-1-nightly.pdf"

	# tweet
	if [[ -n "$URL" && -n "$TWITTER_USER" ]]; then
		sudo -u "$TWITTER_USER" -H twidge update "New nightly build of Contao ER3 documentation is available. $URL/rfccc-1-nightly.pdf"
	fi

	# save the current commit
	echo -n "$CURRENT" > "$CACHE/nightly-commit"
fi
