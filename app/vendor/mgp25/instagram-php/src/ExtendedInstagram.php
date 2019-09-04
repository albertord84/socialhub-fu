<?php

namespace InstagramAPI;

class ExtendedInstagram extends Instagram {
	public function changeUser( $username, $password ) {
        $this->_setUser( $username, $password );
	}
}