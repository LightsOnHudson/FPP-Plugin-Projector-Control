###################################################################
# ProjectorControl - adapted from  AlphaSign protocol client #
#                                                                 #
# Copyright 2014 - Chris Pinkham <cpinkham@bc2va.org>             #
###################################################################
# Half-finished, 
###################################################################
package ProjectorControl;

use IO::Handle;

# Constructor
sub new {
	my $class = shift;
	my $self  = { };
	bless($self, $class);

	$self->{Device} = shift || "/dev/ttyUSB0";
#	printf( "Using %s for sign communication\n", $self->{Device});

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

sub print {
	my $this = shift;
	my $text = shift;
	my $handle = $this->_handle;
	my $data = $text;

	printf( $handle $data );
	#$handle->flush();
}

sub createString {

my $this = shift;
	my $text = shift;
	
	my $data = $text ;

	return $data;


}

1;
