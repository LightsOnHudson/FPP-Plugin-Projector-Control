#!/usr/bin/perl

# ------------------------------------------------------------
# Projector PJLink Control Script
#
# ------------------------------------------------------------
# 2012 IT Services, Truman State University
#
# Maintained by Huan Truong, get the latest version at
# https://gist.github.com/1677547
# ------------------------------------------------------------

use Net::PJLink;
use Getopt::Std;
use Getopt::Long;
use Switch;
use Data::Dumper;

my $ip = $ARGV[0];
my $action = $ARGV[1]; 
my $password = '';
my $param = '';

# Process options.
if ( @ARGV > 0 ) {
    GetOptions( 'p|password=s'=> \$password,
        );
} else {
    $help = <<END;
Usage:
    ./pjlinkutil.pl IP action param [-p password]

    IP: The Hostname/IP Address of the PJLink enabled projector
    action: Action to take, be one of the following
        get_power_state: Get the current power state
        set_power_state: Set power state
            0: Turn off
            1: Turn on
        get_input: Get current input
        set_input: Set input
            {Array: INPUT_RGB,INPUT_VIDEO,INPUT_DIGITAL,INPUT_STORAGE,INPUT_NETWORK} {INPUT_NUMBER}
        
Examples:
    ./pjlinkutil.pl 192.168.0.1 turn 1 -p=1234
        Will turn the projector on
    ./pjlinkutil.pl 192.168.0.1 set_input 1 1 -p=1234
        Will set the current input of the projector to RGB(1) 1
END
    print $help;
    exit 0;
}

my $prj = Net::PJLink->new(
    host       => $ip,
    # keep_alive => 1,
    # auth_password => $password, 
    # This does NOT work -- the documentation is wrong
);

# If authentication is needed then we need to set it.
unless ($password == '') {
    $prj->set_auth_password($password);
}

switch ($action) {
    case 'set_power_state' {
        $param = $ARGV[2];
        print $prj->set_power($param);
    }
    case 'get_input' {
        @current_input = $prj->get_input();
        print $current_input[0][0] . ',' . $current_input[0][1];
    }
    case 'set_input' {
        $param = $ARGV[2];
        $inputnumber = $ARGV[3];
        print $prj->set_input($param,$inputnumber);
    }
    else { # else, default to get power state
        print $prj->get_power();
    }
}

# Close all network connections to the projector.
$prj->close_all_connections;
