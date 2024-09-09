#!/bin/bash
echo "file to check is ${FILE_TO_CHECK_FOR_VERSION_CHANGE}"
revcount=$(git rev-list --all --count)

if [ "$revcount" -gt 1 ]
then
  histdiff=$(git diff --name-only HEAD HEAD~1 | grep "${FILE_TO_CHECK_FOR_VERSION_CHANGE}")
  if [ "$histdiff" = $FILE_TO_CHECK_FOR_VERSION_CHANGE ]
  then
	version=$(grep "Version:" $FILE_TO_CHECK_FOR_VERSION_CHANGE | cut -d ':' -f 2 | xargs)
    if git rev-parse "$version" >/dev/null 2>&1
    then
      echo "Tag already exists; no action taken"
    else
      echo "Creating new tag ${version}"
      changeref=$(git rev-parse HEAD)
      git tag -a ${version} ${changeref} -m "Release version ${version}"
      git push origin --tags
    fi
  else
    echo "${FILE_TO_CHECK_FOR_VERSION_CHANGE} not changed"
  fi
else
  echo "History too short"
fi
