# Clean up scripts

Sometimes there is a disconnect between what Tugboat is tracking, and what actually exists somewhere in Docker. This document is a set of scripts designed to help clean these up.

## Clean up dangling git previews

Git previews are created to interact with a raw “git” style tugboat repo. They are supposed to be deleted after the git operations are completed, but sometimes this doesn’t happen. This can be run on any node, and it will clean up all dangling git previews

```bash
for x in `tugboat ls previews scope=git -q`; do tugboat rm -f $x; done
```

## Delete containers that are no longer linked to Tugboat

This used to be a much bigger problem, but still occurs from time to time. Something happens where Docker doesn’t delete a container when Tugboat asks it to, and the error is lost somewhere in the ether. This command only cleans up orphaned containers on the node it is run on. It can take a while to finish

```bash
for x in `docker ps -a | awk '{print $NF}' | grep -v ^tugboat- | grep -v NAMES`; do tugboat --no-color ls $x -q; done 2>&1 | grep not\ found | awk '{print $1}' | xargs docker rm -f
```

## Clean up images not associated with Tugboat anymore

This one is a multi-step process, and should be run on every node. It removes docker images and image layers that were pulled at one point, but are no longer being used by any services. This tends to clear up quite a lot of space.

1. Find the images that have Tugboat IDs in their tags

```bash
docker images | awk '{print $2}' | sed 's/_anchor//g' | sed 's/_root//g' | sed 's/_container//g' | sort | uniq | grep -E "[0-9a-f]{24}" > ids.txt
```

```bash
docker images | awk '{print $1}' | grep -E "[0-9a-f]{24}" >> ids.txt
```

2. Out of those images, find any that Tugboat is no longer tracking

```bash
for x in `cat ids.txt | sort | uniq`; do tugboat --no-color ls $x -q; done 2>&1 | grep not\ found | awk '{print $1}' > not-found.txt
```

3. CHECKPOINT: If there is nothing in not-found.txt at this point, skip to step 5

```bash
cat not-found.txt
```

4. Delete the images listed in not-found.txt

```bash
for x in `cat not-found.txt`; do docker images | grep $x | awk '{print $1 ":" $2 }'; done | xargs docker rmi
```

5. Remove any other unused images NEVER USE -a WITH THIS COMMAND

```bash
docker image prune
```

## Clean up extant screenshots from absent previews.

Sometimes screenshots or other data get left behind in /opt/tugboat.local/data, eventually using up disk space on that volume.

```bash
for x in $(find /opt/tugboat.local/data/ -regextype posix-extended -regex '.*/[0-9a-f]{24}'); do
  id=$(basename "$x")
  tugboat ls "$id" --no-color -q 2>&1 | grep -q 'not found' && rm -rv "$x"
done
```

## Stop containers that belong to suspended previews

:information_source: If a server is experiencing high CPU load or memory usage, this section of snippets is helpful to stop any running containers that shouldn’t be running to free up CPU and memory.

When a preview is suspended, all of its service containers are supposed to be stopped. Sometimes that doesn’t happen. This will stop any service containers on the current node that belong to suspended previews.

```bash
for x in $(docker ps | awk '{print $NF}'); do tugboat --no-color ls $x 2>&1; done | grep suspended | awk '{print $1}' | xargs docker stop
```

Similarly, check for containers that belong to stopped previews

```bash
for x in $(docker ps | awk '{print $NF}'); do tugboat --no-color ls $x 2>&1; done | grep stopped | awk '{print $1}' | xargs docker stop
```

If you need to suspend all running previews for a repo, you can run the following snippet. This is handy if there are a lot of previews that are being rebuilt and you would like to suspend the recently built previews to free up CPU and RAM.

```bash
repo=6102ce11cafc876d36a86239
for x in $(tugboat ls previews repo=$repo -j | jq -r '.[] | select( (.state == "ready" or .state == "failed") and .anchor == false ) | .id'); do (tugboat suspend $x &); done
```
