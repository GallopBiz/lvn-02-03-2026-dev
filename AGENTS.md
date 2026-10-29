# Workspace Rules & Pair Programming Guidelines

## 1. File Modification & Tool Usage (Accept / Reject Changes)
- **Always use `edit_file` or `write_file` directly**: Every codebase change or file creation MUST be executed using the agent's built-in `edit_file` or `write_file` tool.
- **Never use runner scripts or shell redirection to edit codebase files**: Do not run PHP scripts, Node scripts, Python scripts, or PowerShell/bash commands to write or overwrite project code files. Doing so bypasses the IDE's pair programming tool tracker, preventing the user from seeing the interactive diff preview with **Accept** and **Reject** buttons.
- **Line Ending Handling on Windows**: If `edit_file` encounters a target mismatch due to CRLF line endings, normalize the file's line endings to LF first and then execute the change using `edit_file`.

## 2. Version Control
- **Do not commit changes**: Never run `git commit` or stage changes with `git add` unless specifically instructed by the user. All modifications must remain unstaged in the working tree for user inspection and review.

