#!/bin/bash
pushd $(dirname $(which $0))
target_PWD=$(readlink -f .)
/opt/fpp/scripts/update_plugin ${target_PWD##*/}
/usr/bin/sudo /bin/chmod a+w /dev/tty*
echo ; echo “The plugin is installing the required library.” ; echo
echo ; echo “This can take a few minutes.” ; echo

PERL_MM_USE_DEFAULT=1 cpan install Net::PJLink

echo ; echo “Please restart fppd for new FPP Commands to be visible.” ; echo
popd
