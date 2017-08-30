#!/bin/bash
pushd $(dirname $(which $0))
target_PWD=$(readlink -f .)
exec /opt/fpp/scripts/update_plugin ${target_PWD##*/}
/usr/bin/sudo /bin/chmod a+w /dev/tty*
popd
