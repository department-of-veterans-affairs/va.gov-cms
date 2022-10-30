## Pull Request Advice (.github/workflows/config/advice/*.md)

The files in `.github/workflows/config/advice` are used to populate comments that may be added to your PR, depending on the files that are changed as of the latest commit.

They should follow these guidelines:

- Written in [GitHub-Flavored Markdown](https://github.github.com/gfm/), with a file extension of `.md`.
- An H2 subtitle (e.g. `## Title`) at the top of the document.
- A brief explanation about what the file or directory contains.
- The filename should be equal to the name of the subject filename or directory with each occurrence of one or more non-alphanumeric characters (including directory separators and the dot in the file extension) replaced with an underscore.  This can be generated for you by running `echo $filename | sed -E 's/[^[:alnum:]]+/_/g'`.
- As appealing as it is to add checklists, the state of any checklists in the generated comment will be reset completely on the next commit, including merges.  It's probably best that these remain within the initial comment of the PR.  (This is arguable and open to discussion.)

You're encouraged to add links, emoji, formatting, whatever will help to make the file more readable when it is presented.

Note that these files are not pulled from the PR branch, but from the latest commit on the `main` branch.  Consequently, you cannot see changes made to these files in the context of a PR.  No change will take effect until the PR is merged.  Therefore, these files should generally be treated as documentation and can be edited freely outside of the GitHub Flow model.
