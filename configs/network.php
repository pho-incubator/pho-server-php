<?php
return array(
	"relationships" => "follow", // follow or friend
	"feed_show" => 10, // how many posts to show by default
	"feed_autoload" => true, // whether or not enable autoload for the feed
	"discoverable" => true, // if set true, submits the site to google and pho
	"max_members" => -1, // -1 to make it unlimited, you can set a maximum number of members, which may create a sense of urgency for people to join. You can change this number at any time, although if it is lower than the existing number of members, it won't have any other impact other than not accepting any new members.
	"max_member_invitations_per_week" => -1, // members can invite unlimited number of people per week
	"max_member_invitations_per_day" => -1, // members can invite unlimited number of people per day
	"max_member_invitations_per_month" => -1, // members can invite unlimited number of people per month
	"max_member_invitations_all_time" => -1, // members can invite unlimited number of people
	"enable_wall" => true, // can members make comments about each other?
	"pm_multiple" => true, // messaging multiple people allowed?
);
