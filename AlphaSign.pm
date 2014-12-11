###################################################################
# AlphaSign.pm - Perl implementation of AlphaSign protocol client #
#                                                                 #
# Copyright 2014 - Chris Pinkham <cpinkham@bc2va.org>             #
###################################################################
# Half-finished, but working for my sign so far.
###################################################################
package AlphaSign;

use IO::Handle;

# Constructor
sub new {
	my $class = shift;
	my $self  = { };
	bless($self, $class);

	$self->{Device} = shift || "/dev/ttyUSB0";
#	printf( "Using %s for sign communication\n", $self->{Device});

	$self->{CTRL_INIT}      = "\x00\x00\x00\x00\x00\x00\x00";
	$self->{CTRL_NUL}       = "\x00"; # NULL
	$self->{CTRL_SOH}       = "\x01"; # Start Of Heading
	$self->{CTRL_STX}       = "\x02"; # Start of Text
	$self->{CTRL_ETX}       = "\x03"; # End of Text
	$self->{CTRL_EOT}       = "\x04"; # End Of Transmission
	$self->{CTRL_DBLWDOFF}  = "\x1d\x31\x30"; # Double Width Off
	$self->{CTRL_DBLWDON}   = "\x1d\x31\x31"; # Double Width On
	$self->{CTRL_DBLHTOFF}  = "\x1d\x32\x30"; # Double Height Off
	$self->{CTRL_DBLHTON}   = "\x1d\x32\x31"; # Double Height On
	$self->{CTRL_DBLSZOFF}  = $self->{CTRL_DBLWDOFF} .
				  $self->{CTRL_DBLHTOFF};
	$self->{CTRL_DBLSZON}   = $self->{CTRL_DBLWDON} .
				  $self->{CTRL_DBLHTON};
	$self->{CTRL_DESCOFF}   = "\x06\x30"; # True Descenders Off
	$self->{CTRL_DESCON}    = "\x06\x31"; # True Descenders On

	$self->{CTRL_NEWLINE}   = "\x0D"; # New Line

	$self->{CTRL_ESC}       = "\x1b"; # Escape

	$self->{CTRL_SPEED1}    = "\x15"; # Speed 1 (slowest)
	$self->{CTRL_SPEED2}    = "\x16"; # Speed 2
	$self->{CTRL_SPEED3}    = "\x17"; # Speed 3
	$self->{CTRL_SPEED4}    = "\x18"; # Speed 4
	$self->{CTRL_SPEED5}    = "\x19"; # Speed 5 (fastest)

	$self->{COLOR_RED}      = "\x1C\x31";
	$self->{COLOR_GREEN}    = "\x1C\x32";
	$self->{COLOR_AMBER}    = "\x1C\x33";
	$self->{COLOR_DIMRED}   = "\x1C\x34";
	$self->{COLOR_DIMGREEN} = "\x1C\x35";
	$self->{COLOR_BROWN}    = "\x1C\x36";
	$self->{COLOR_ORANGE}   = "\x1C\x37";
	$self->{COLOR_YELLOW}   = "\x1C\x38";
	$self->{COLOR_RAINBOW1} = "\x1C\x39";
	$self->{COLOR_RAINBOW2} = "\x1C\x41";
	$self->{COLOR_COLORMIX} = "\x1C\x42";
	$self->{COLOR_AUTO}     = "\x1C\x43";

	$self->{MODE_ROTATE}    = "a"; # Msg travels left to right
	$self->{MODE_HOLD}      = "b"; # Msg remains stationary
	$self->{MODE_FLASH}     = "c"; # Msg stationary and flashes
	# 'd' is reserved
	$self->{MODE_ROLLUP}    = "e"; # Prev Msg pushed up by new
	$self->{MODE_ROLLDOWN}  = "f"; # Prev Msg pushed down by new
	$self->{MODE_ROLLLEFT}  = "g"; # Prev Msg pushed left by new
	$self->{MODE_ROLLRIGHT} = "h"; # Prev Msg pushed right by new
	$self->{MODE_WIPEUP}    = "i"; # Prev Msg wiped up by new
	$self->{MODE_WIPEDOWN}  = "j"; # Prev Msg wiped down by new
	$self->{MODE_WIPELEFT}  = "k"; # Prev Msg wiped left by new
	$self->{MODE_WIPERIGHT} = "l"; # Prev Msg wiped right by new
	$self->{MODE_SCROLL}    = "m"; # Msg pushes bottom line to top
	$self->{MODE_SPECIAL}   = "n"; # Followed by Special Specifier
	$self->{MODE_AUTOMODE}  = "o"; # Various Modes called
	$self->{MODE_ROLLIN}    = "p"; # Prev Msg pushed to center by new
	$self->{MODE_ROLLOUT}   = "q"; # Prev Msg pushed to sides by new
	$self->{MODE_WIPEIN}    = "r";
	$self->{MODE_WIPEOUT}   = "s";
	$self->{MODE_COMPROT}   = "t"; # Compressed Rotate
	$self->{MODE_EXPLODE}   = "u";
	$self->{MODE_CLOCK}     = "v";

	# FIXME, fill in the rest of the special modes here
	$self->{MODE_SPECIAL_TWINKLE}   = "0"; # Msg will Twinkle
	$self->{MODE_SPECIAL_SPARKLE}   = "1"; # Msg will Twinkle

	# Some Defaults
	$self->{SIGNID}        = "00"; # All Signs
	$self->{MODE_CURRENT}  = $self->{MODE_HOLD};
	$self->{COLOR_CURRENT} = $self->{COLOR_RED};

	return $self;
}

sub _handle {
	my $this = shift;
	if (defined($this->{Handle})) {
		return $this->{Handle};
	}

	my $handle;
	open( $handle, "> " . $this->{Device} )
		|| die "Can't open " . $this->{Device} . ": $!";

	$this->{Handle} = $handle;

	return $this->{Handle};
}

sub SetSignID {
	my $this = shift;
	$this->{SIGNID} = shift || "00";
}

sub SetColor {
	my $this = shift;
	$this->{COLOR_CURRENT} = shift || $self->{COLOR_RED};
}

sub SetMode {
	my $this = shift;
	$this->{MODE_CURRENT} = shift || $self->{MODE_HOLD};
}

sub print {
	my $this = shift;
	my $text = shift;
	my $handle = $this->_handle;
	my $data =
		$this->{CTRL_INIT} . $this->{CTRL_SOH} . "Z" .
		$this->{SIGNID} .
		$this->{CTRL_STX} . "AA" . $this->{CTRL_ESC} .
		"0" . $this->{MODE_CURRENT} . $this->{COLOR_CURRENT} .
		$text . $this->{CTRL_EOT};

	printf( $handle $data );
	$handle->flush();
}

1;
