<?php
return array(
	"format"=>"text", // text|file // audio|photo|video|file 
	// "allowed_media"=> "mp4|..|..",
	"max_length"=>255, // characters|seconds|mb (String)
	"cutoff_length"=>-1, // -1 or 0 no cutoff
	"title"=>false,
	"title_length"=>-1, // characters
	"ephemeral"=>-1, // seconds
	"tags"=>true, // #
	"mentions"=>true, // @
	"markdown"=>false,
	"edit"=>false, // characters
	"keep_history"=>false,
	"show_history"=>false, 
	"reactions"=>"upvote|comments", // vote|comments|custom:(future)
);




