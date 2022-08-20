# Silence output slightly
# .SILENT:

DB := dhil_amplify
PROJECT := amplify

# Override any of the options above by copying them to makefile.local
-include Makefile.local

include etc/Makefile

## -- Local make file
