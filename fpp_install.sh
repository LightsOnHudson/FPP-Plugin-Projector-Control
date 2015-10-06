#!/bin/bash
pushd $(dirname $(which $0))
if [ ! -e "/usr/local/share/perl/5.14.2/Net/PJLink.pm" ]
then
    (echo y;echo y;echo o conf prerequisites_policy follow;echo o conf commit) | cpan
    cpan install Net::PJLink
fi
popd
