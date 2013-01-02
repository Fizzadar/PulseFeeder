<!DOCTYPE html>
<html>
<head>
	<!--title-->
	<title>Pulse Feeder</title>
	
	<!--style-->
	<style type="text/css">
		/*
		----------------------------Basics*/
		body {
			font-family: Trebuchet MS, Arial;
			font-size: 14px;
			background: #FFFFFF;
			color: #666666;
			margin: 0;
			height: 100%;
			width: 100%;
		}
		
		a {
			color: #E023BD;
			text-decoration: none;
		}
		a:hover {
			color: #11B1E0;
		}
		
		h1, h2, h3 {
			font-weight: normal;
		}
		h1, h2 {
			font-size: 18px;
		}
		
		/*
		----------------------------Common Classes*/
		.left {
			float: left;
		}
		.right {
			float: right;
		}
		
		/*
		----------------------------Layout*/
		div#wrap {
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			overflow-x: hidden;
		}
		
		div#bar {
			position: fixed;
			top: 0;
			width: 100%;
			height: 16px;
			background: #F7F7F7;
			border-bottom: 1px solid #D7D7D7;
			padding: 10px 0;
			text-indent: 21px;
			z-index: 999;
		}
			div#bar span.right {
				margin-right: 21px;
			}
		
		div#settings {
			position: fixed;
			top: 37px;
			left: 0;
			width: 100%;
			padding: 10px 0;
			background: #F7F7F7;
			border-bottom: 1px solid #D7D7D7;
			z-index: 999;
			display: none;
		}
			div#settings small, div#settings h3, div#settings form {
				margin-left: 10px;
			}
		
		div#currentFeed {
			width: 300px;
			float: left;
			padding: 0 10px;
			position: absolute;
			top: 37px;
			bottom: 0;
			margin-left: -350px;
			background: #F7F7F7;
			border-right: 1px solid #D7D7D7;
			overflow: auto;
		}
		
		div#feedItem {
			position: absolute;
			left: 350px;
			right: 0px;
			padding-right: 30px;
			top: 37px;
			bottom: 0px;
			overflow: auto;
			display: none;
		}
		
		div#feeds {
			margin-top: 60px;
		}
		
		div.feed {
			float: left;
			width: 100%;
			display: block;
			padding: 0 10px;
			opacity: 0.8;
			border: 1px solid #FFFFFF;
			padding-bottom: 60px;
			padding-left: 30px;
			margin-bottom: 60px;
			border-bottom: 1px dotted #E7E7E7;
		}
			div.feed a.rightarrow, div.feed a.leftarrow {
				right: 0;
				width: 100px;
				margin-top: 30px;
				padding-top: 70px;
				height: 80px;
				position: absolute;
				background: url( 'item_bg.png' );
				z-index: 99;
				font-size: 200px;
				line-height: 0px;
			}
			div.feed a.leftarrow {
				left: 0;
				display: none;
			}
			div.feed h1 {
				position: absolute;
				left: 30px;
				margin-top: 0;
			}
			div.feed img {
				max-width: 100%;
			}
			div.feed div.item {
				position: relative;
				float: left;
				width: 200px;
				height: 150px;
				margin-right: 20px;
				margin-top: 40px;
				overflow: hidden;
				-webkit-border-radius: 5px;
				-moz-border-radius: 5px;
			}
				div.feed div.item h2 {
					margin-top: 0;
					font-weight: bold;
				}
				div.feed div.item h2.image {
					background: url( 'item_bg.png' );
					position: absolute;
					bottom: 0;
					margin-bottom: 0;
				}
					div.feed div.item h2.image a {
						
					}
					div.feed div.item h2 a {
						color: #1A1B1B;
					}
					div.feed div.item h2 a:hover {
						color: #11B1E0;
					}
					
		div#imagecheck {
			display: none;
		}
	</style>
	
	<!--js-->
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
	<script type="text/javascript" src="jgfeed.js"></script>
	<script type="text/javascript">
		/*
			setup some vars, default feeds
		*/
		var view = 'feeds';
		var feeds = [
			'http://feeds.bbci.co.uk/news/world/rss.xml',
			'http://feeds.feedburner.com/nettuts',
		];
		var feedcontent = [];
		var feedinfo = [];
		var time = null;
		var movetime = 0;
		
		/*
			load local feeds!
		*/
		if( localStorage.getItem( 'pulsefeeds' ) ) {
			thefeeds = JSON.parse( localStorage.getItem( 'pulsefeeds' ) );
			if( thefeeds.length > 0 ) {
				feeds = thefeeds;
			}
		}

		/*
			change from multi-feed to one feed
		*/
		function toggleView( feed ) {
			//one feed >> multi feed
			if( view == 'feed' ) {
				view = 'feeds';
				//allow long body again
				$( '#wrap' ).css( 'overflow-y', 'auto' );
				//3-step animation
				$( '#feedItem' ).slideUp( 100, function() {
					$( '#currentFeed' ).animate({
						marginLeft: '-350px'
					}, 100, function() {
							$( '#feeds' ).animate({
								marginLeft: '0'
							}, 200 );
							$( '#feeds .feed h1' ).animate({
								left: '20'
							}, 200 );
						}
					);
				});
				//remove current feed content
				$( '#currentFeed' ).html( '' );
				$( '.leftarrow' ).css( 'visibility', 'visible' );
				$( '.rightarrow' ).css( 'visibility', 'visible' );
			//multi feed >> one feed
			} else {
				$( '.leftarrow' ).css( 'visibility', 'hidden' );
				$( '.rightarrow' ).css( 'visibility', 'hidden' );
				$( '#wrap' ).scrollTop( 0 );
				view = 'feed';
				//no long body
				$( '#wrap' ).css( 'overflow-y', 'hidden' );
				//set current feed content
				loadCurrent( feed );
				$( '#currentFeed .backlink' ).html( '<a href="#" onclick="toggleView(); return false;">&#171; back</a>' );
				//3-step animation
				$( '#feeds .feed h1' ).animate({
					left: '-1000'
				}, 200 );
				$( '#feeds' ).animate({
					marginLeft: '-' + parseInt( $( '#feeds' ).css( 'width' ) ) + 100
				}, 200, function() {
						$( '#currentFeed' ).animate({
							marginLeft: '0'
						}, 100, function() {
									$( '#feedItem' ).slideDown( 100 );
								}
						);
					}
				);
			}
		}
		
		/*
			load up the current sidebar
		*/
		function loadCurrent( feed ) {
			$( '#currentFeed' ).html( '<h1><a href="' + feedinfo[feed][1] + '">' + feedinfo[feed][0] + '</a></h1><span class="backlink"></span>' );
			$.each( feedcontent[feed], function( item ) {
				$( '#currentFeed' ).append( '<h2><a href="#" onclick="loadItem( ' + feed + ', ' + item + ' ); return false;">' + feedcontent[feed][item][0] + '</a></h2>' + feedcontent[feed][item][3] );
			});
		}
		
		/*
			load an item from the feedcontent into item
		*/
		function loadItem( feed, entry ) {
			$( '#feedItem' ).slideUp( 500, function() {
				$( '#feedItem' ).html( '<h1>' + feedcontent[feed][entry][0] + ' <small><a target="_blank" href="' + feedcontent[feed][entry][2] + '">view full &#187;</a></small></h1>' + feedcontent[feed][entry][1] );
				$( '#feedItem' ).slideDown( 500 );
			});
		}
		
		/*
			add feed to array
		*/
		function addFeed( feed ) {
			//cosmetic
			if( feeds.length == 0 ) {
				$( '#feedslist' ).html( '<li>' + feed + '</li>' );
			} else {
				$( '#feedslist' ).append( '<li>' + feed + '</li>' );
			}
			//add it
			feeds[feeds.length] = feed;
			//save it
			saveFeeds();
			//re-load
			loadUp();
		}
		
		/*
			delete feed from array
		*/
		function deleteFeed( feed ) {
			//delete it
			feeds.splice( feed, 1 );
			//save it
			saveFeeds();
			//re-load
			loadUp();
		}
		
		/*
			save feeds to local storage
		*/
		function saveFeeds() {
			localStorage.setItem( 'pulsefeeds', JSON.stringify( feeds ) );
		}
		
		/*
			rawr! load shit function
		*/
		function loadUp() {
			//back to multi feeds
			if( view == 'feed' ) {
				toggleView();
			}
			
			//set the width
			//$( '#feeds' ).css( 'width', 322 * feeds.length );
			$( '#feeds' ).css( 'width', 222 * 50 );
			
			//reset!
			$( '#feeds' ).html( '' );
			$( '#feedslist' ).html( '' );
			
			//loop the feeds
			$.each( feeds, function( feed ) {
				//add the feed to feedcontent
				feedcontent[feed] = [];
				//add the feed html
				$( '#feeds' ).append( '<div class="feed" id="feed-' + feed + '"><br /><br />loading feed...<br /><br /><img src="loader.gif" alt="" /></div>' );
				$( '#feedslist' ).append( '<li>' + feeds[feed] + ' &bull; <a href="#" onclick="deleteFeed( ' + feed + ' ); $( this ).parent().remove(); return false;">delete</a></li>' );
				//load the feed itself
				$.jGFeed( feeds[feed], function( data ) {
					$( '#feed-' + feed ).html( '<h1><a href="#" onclick="loadItem( ' + feed + ', 0 ); toggleView( ' + feed + ' ); return false;">' + data.title + '</a></h1><a href="#" onclick="moveLeft( $( this ).parent(), ' + data.entries.length + ' ); return false;" class="rightarrow">&#187;</a><a href="#" onclick="moveRight( $( this ).parent(), ' + data.entries.length + ' ); return false;" class="leftarrow">&#171;</a>' );
					feedinfo[feed] = [ data.title, data.link ];
					$.each( data.entries, function( entry ) {
						//lets find an image!
						var img = '';
						var content = data.entries[entry].contentSnippet;
						var h2class = '';
						var image = data.entries[entry].content.match( /<img[^<>]+src="([^"]+)"[^<>]+>/ );
						if( image ) {
							//time to check image size
							$( '#imagecheck' ).append( '<img src="' + image[1] + '" alt="" id="img' + feed + entry + '" />' );
							if( document.getElementById( 'img' + feed + entry ).width > 100 && document.getElementById( 'img' + feed + entry ).height > 100 ) {
								img = 'background: url( ' + image[1] + ' ) left top no-repeat';
								content = '';
								h2class = 'image';
							}
						}
						//write the content snippet
						$( '#feed-' + feed ).append( '<div class="item" style="' + img + '"><h2 class="' + h2class + '"><a href="#" onclick="loadItem( ' + feed + ', ' + entry + ' ); if( view == \'feeds\' ) { toggleView( ' + feed + ' ); } return false;">' + data.entries[entry].title + '</a></h2><p>' + content + '</p></div>' );
						//add feedcontent
						feedcontent[feed][entry] = [ data.entries[entry].title, data.entries[entry].content, data.entries[entry].link, data.entries[entry].contentSnippet ];
						//add feed info
					});
				}, 50 );
			});
			
			//settings thing
			if( feeds.length == 0 ) {
				$( '#feedslist' ).append( '<li>none!</li>' );
			}
		}
		
		/*
			update arrows
		*/
		function processArrows( feed, change, limit ) {
			limit = limit + 440;
			if( ( parseInt( $( feed ).css( 'marginLeft' ) ) + change ) < 0 ) {
				$( feed ).children( '.leftarrow' ).css( 'display', 'block' );
			} else {
				$( feed ).children( '.leftarrow' ).css( 'display', 'none' );
			}
			if( parseInt( $( feed ).css( 'marginLeft' ) ) < limit ) {
				$( feed ).children( '.rightarrow' ).css( 'display', 'none' );
			} else {
				$( feed ).children( '.rightarrow' ).css( 'display', 'block' );
			}
		}
		
		/*
			move feed functions
		*/
		function moveLeft( feed, limit ) {
			var limit = ( -limit * 222 ) + 880;
			time = new Date();
			if( parseInt( $( feed ).css( 'marginLeft' ) ) > limit && movetime < time.getTime() ) {
				$( feed ).animate({
					marginLeft: parseInt( $( feed ).css( 'marginLeft' ) ) - 440,
					marginRight: parseInt( $( feed ).css( 'marginRight' ) ) + 440
				}, 200 );
				movetime = time.getTime() + 250;
			}
			processArrows( feed, -440, limit );
		}
		function moveRight( feed ) {
			time = new Date();
			if( parseInt( $( feed ).css( 'marginLeft' ) ) < 0 && movetime < time.getTime() ) {
				$( feed ).animate({
					marginLeft: parseInt( $( feed ).css( 'marginLeft' ) ) + 440,
					marginRight: parseInt( $( feed ).css( 'marginRight' ) ) - 440
				}, 200 );
				movetime = time.getTime() + 250;
			}
			processArrows( feed, 440 );
		}
	</script>
</head>
<body><div id="wrap">
	<div id="bar">
		<span class="left">
			<a href="#" onclick="loadUp(); return false;">reload</a>
		</span>
		<span class="right">
			<a href="#" onclick="$( '#settings' ).slideToggle( 300 ); return false;">settings</a>
		</span>
	</div><!--end bar-->
	
	<div id="settings">
		<small><a href="#" onclick="$( '#settings' ).slideToggle( 300 ); return false;">close</a></small>
		<h3>Current Feeds</h3>
		<ul id="feedslist">
		</ul>
		
		<h3>Add Feed</h3>
		<form action="" method="get" onsubmit="addFeed( $( '#addfeed' ).val() ); return false;">
			<input type="text" id="addfeed" value="" />
			<input type="submit" value="Add Feed &#187;" />
		</form>
	</div><!--end settings-->
	
	<div id="currentFeed">
	</div><!--end currentFeed-->
	
	<div id="feedItem">
	</div><!--end feedItem-->
	
	<div id="feeds">
	</div><!--end feeds-->
	
	<div id="imagecheck">
	</div><!--end imagecheck-->
	
	<script type="text/javascript">
		/*
			start up (once to get basic, second fixes images)
		*/
		loadUp();
		loadUp();
	</script>
</div><!--end wrap--></body>
</html>