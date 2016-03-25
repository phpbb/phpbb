#!/usr/bin/env python
# Remove ending PHP tags '?>'
# @author Oleg Pudeyev
# @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2

import sys, os, os.path, optparse

def error(message, code):
    print >>sys.stderr, message
    exit(code)


parser = optparse.OptionParser()
parser.add_option('-a', '--aggressive', help='Remove ending tags when they are followed by whitespace', action='store_true')
options, args = parser.parse_args()

if len(args) != 1:
    parser.usage()
    error("Usage: remove-php-end-tags path", 2)

path = args[0]

if not os.path.exists(path):
    error("Path does not exist: %s" % path, 3)

if options.aggressive:
    import re
    
    fix_re = re.compile(r'\s*\?>\s*$')
    def fix_content(content):
        content = fix_re.sub(r'\n', content)
        return content
else:
    def fix_content(content):
        if content.endswith('?>'):
            content = content[:-2].strip() + "\n"
        return content

def process_file(path):
    f = open(path)
    try:
        content = f.read()
    finally:
        f.close()
    fixed_content = fix_content(content)
    if content != fixed_content:
        f = open(path, 'w')
        try:
            f.write(fixed_content)
        finally:
            f.close()

def process_dir(path):
    for root, dirs, files in os.walk(path):
        if '.svn' in dirs:
            dirs.remove('.svn')
        for file in files:
            if file.endswith('.php'):
                path = os.path.join(root, file)
                process_file(path)

if os.path.isdir(path):
    process_dir(path)
else:
    process_file(path)
