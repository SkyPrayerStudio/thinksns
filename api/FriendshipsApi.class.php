<?php
			if ($result == '00' || $result == '10') {
				$result = array('is_followed' => 'unfollow');
			}else if ($result == '13') {
				$result = array('is_followed' => 'eachfollow');
			}else {
				$result = array('is_followed' => 'havefollow');
			}
			return $result;
			return array('is_followed' => $result ? 'unfollow' : 'havefollow');			