<?php

class debug extends plugins {   

	function __construct(){
		parent::__construct();
		$this->registry = registry::getInstance();
		
		$this->addAction('index_init', 'init');
		$this->addAction('index_load', 'addToolbar');
	}
	
	public function init(){
		$action = isset($_GET['action'])?$_GET['action']:'';
		switch($action){
			case 'create_tables': $this->create_tables(); $this->redirect('/'); break;
			case 'delete_tables': $this->delete_tables(); $this->redirect('/'); break;
			case 'generate_configurations': $this->generate_configurations(); break;
			case 'delete_configurations': $this->delete_configurations(); break;
			case 'add_posts': $this->add_posts(); break;
			case 'delete_posts': $this->delete_posts(); $this->redirect('/');break;
			case 'add_tags': $this->add_tags(); break;
			case 'delete_tables': $this->delete_tables(); $this->redirect('/'); break;
			case 'add_comments': $this->add_comments(); break;
			case 'delete_commentss': $this->delete_comments(); break;
		}
	}
	
	public function addToolbar(){
	?>
		<style>
			div.toolbar{
				font-size:.75em;
				background-color:yellow;
				padding:5px;
			}
			div.toolbar ol{padding:20px 0 0 0;}
			div.toolbar ol li{
				list-style-type: decimal;
				list-style-position: inside;
				padding:0 0 0 20px;
			}
		</style>
		<div class="toolbar">
			<h1>DEBUG MODE</h1>
			<ol>
				<li><a href="?action=create_tables">Create tables</a></li>
				<li><a href="?action=delete_tables">Delete tables</a></li>
				<li><a href="?action=generate_configurations">Generate Configurations</a></li>
				<li><a href="?action=delete_configurations">Delete Configurations</a></li>
				<li><a href="?action=add_posts">Add Posts</a></li>
				<li><a href="?action=delete_posts">Delete Posts</a></li>
				<li><a href="?action=add_tags">Add Tags</a></li>
				<li><a href="?action=delete_tables">Delete Tags</a></li>
				<li><a href="?action=add_comments">Add comments</a></li>
				<li><a href="?action=delete_comments">Delete comments</a></li>
			</ol>
		</div>
	<?php
	}
	
	public function delete_tables(){
		$M = mysqli_db::getInstance();
		$sqls = array();
		
		$sqls[] = "DROP TABLE IF EXISTS `comments`;";
		$sqls[] = "DROP TABLE IF EXISTS `configurations`;";
		$sqls[] = "DROP TABLE IF EXISTS `files`;";
		$sqls[] = "DROP TABLE IF EXISTS `links`;";
		$sqls[] = "DROP TABLE IF EXISTS `posts`;";
		$sqls[] = "DROP TABLE IF EXISTS `tags`;";
		$sqls[] = "DROP TABLE IF EXISTS `tags_rel`;";
		$sqls[] = "DROP TABLE IF EXISTS `users`;";
		
		foreach($sqls as $sql){
			$M->query($sql);
		}
	}
	
	public function create_tables(){
		$this->delete_tables();
		
		$M = mysqli_db::getInstance();
		
		$sqls = array();
		
		$sqls[] = "CREATE TABLE IF NOT EXISTS `comments` (
  `suscribe` varchar(255) default NULL,
  `user_id` varchar(255) default NULL,
  `type` varchar(255) default NULL,
  `status` varchar(255) default NULL,
  `content` text,
  `IP` varchar(255) default NULL,
  `url` varchar(255) default NULL,
  `email` varchar(255) default NULL,
  `author` varchar(255) default NULL,
  `created` datetime default NULL,
  `modified` datetime default NULL,
  `ID_post` int(10) unsigned NOT NULL,
  `ID` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
";
		$sqls[] = "CREATE TABLE IF NOT EXISTS `configurations` (
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id` int(11) NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
		
		$sqls[] = "CREATE TABLE IF NOT EXISTS `files` (
  `id_file` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `hotlink` int(1) NOT NULL,
  `last_access` datetime NOT NULL default '0000-00-00 00:00:00',
  `count` int(11) NOT NULL default '0',
  `stats` int(1) NOT NULL,
  `url` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  PRIMARY KEY  (`id_file`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
		
		$sqls[] = "CREATE TABLE IF NOT EXISTS `links` (
  `name` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `type` varchar(15) character set utf8 collate utf8_bin NOT NULL default 'external',
  `created` datetime default NULL,
  `modified` datetime default NULL,
  `ID` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
		
		$sqls[] = "CREATE TABLE IF NOT EXISTS `posts` (
  `urlfriendly` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text,
  `status` varchar(50) default NULL,
  `ID` int(10) unsigned NOT NULL auto_increment,
  `id_user` int(11) NOT NULL,
  `created` datetime default NULL,
  `modified` datetime default NULL,
  PRIMARY KEY  (`ID`),
  FULLTEXT KEY `title` (`title`,`content`,`urlfriendly`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
		
		$sqls[] = "CREATE TABLE IF NOT EXISTS `tags` (
  `tag_id` int(11) NOT NULL auto_increment,
  `tag` varchar(255) NOT NULL,
  `urlfriendly` varchar(255) NOT NULL,
  PRIMARY KEY  (`tag_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
		
		$sqls[] = "CREATE TABLE IF NOT EXISTS `tags_rel` (
  `tag_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		
		$sqls[] = "CREATE TABLE IF NOT EXISTS `users` (
  `id_user` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) default NULL,
  `login` varchar(100) NOT NULL default '',
  `password` varchar(64) NOT NULL default '',
  `email` varchar(100) default NULL,
  `website` varchar(150) default NULL,
  `about` text,
  `created` datetime default NULL,
  `modified` datetime default NULL,
  PRIMARY KEY  (`id_user`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
		
		foreach($sqls as $sql){
			$M->query($sql);
		}
	}
	
	public function generate_configurations(){
		$this->delete_configurations();
		$M = mysqli_db::getInstance();
		
		$sql = "INSERT INTO `configurations` (`name`, `value`, `id_user`, `id`) VALUES
('blog_name', 'Codice CMS', 1, 1),
('blog_description', 'Content management made easy', 1, 2),
('blog_siteurl', 'http://localhost/codice/CodiceCMS', 1, 3),
('blog_posts_per_page', '3', 1, 5),
('posts_per_page', '15', 1, 6),
('blog_upload_folder', 'uploads', 1, 8);";
		$M->query($sql);
	}
	
	public function delete_configurations(){
		$M = mysqli_db::getInstance();
		
		$sql = "TRUNCATE TABLE `configurations`";
		
		$M->query($sql);
	}
	
	public function add_posts(){
		$sql = "SU5TRVJUIElOVE8gYHBvc3RzYCAoYHVybGZyaWVuZGx5YCwgYHRpdGxlYCwgYGNvbnRlbnRgLCBg
c3RhdHVzYCwgYElEYCwgYGlkX3VzZXJgLCBgY3JlYXRlZGAsIGBtb2RpZmllZGApIFZBTFVFUw0K
KCdQcnVlYmEnLCAnUHJ1ZWJhJywgJzxoMz5QcnVlYmE8L2gzPlxyXG5cclxuPHA+RXN0byBlcyB1
bmEgInBydWViYSI6ICEiIyQlJi8oKT0/wqE8L3A+XHJcblxyXG48dWw+XHJcbgk8bGk+UHJ1ZWJh
IHVubzwvbGk+XHJcbgk8bGk+UHJ1ZWJhIGRvczwvbGk+XHJcbgk8bGk+UHJ1ZWJhIHRyZXM8L2xp
PlxyXG4JPGxpPlBydWViYSBjdWF0cm88L2xpPlxyXG4JPGxpIGlkPSJib3Rvbi1jaW5jbyI+Y2lu
Y288L2xpPlxyXG48L3VsPlxyXG5cclxuPGgzPjw/cGhwIGVjaG8gJHRvdGFsID0gcmFuZCgyLDUp
Oz8+IHDDoXJyYWZvczwvaDM+XHJcblxyXG48cD5FbCBzaWd1aWVudGUgY8OzZGlnbyBlcyB1biBl
amVtcGxvIFg6PC9wPlxyXG5cclxuPGNvZGUgbGFuZz0icGhwIiB0aXRsZT0iNi05LDExLDEyIj4k
cCA9IG5ldyBwYWdpbmF0aW9uO1xyXG4kcC0+SXRlbXMoMTAwMCk7XHJcbiRwLT5saW1pdCg1KTtc
clxuJHAtPnRhcmdldCgiIyIpO1xyXG4kcC0+Y3VycmVudFBhZ2UoMTEpO1xyXG4kcC0+Y2hhbmdl
Q2xhc3MoImpvZ2dlciIpO1xyXG4kcC0+c2hvdygpO1xyXG4kcC0+Y2hhbmdlQ2xhc3MoInRyZXMi
KTtcclxuJHAtPnNob3coKTtcclxuJHAtPmN1cnJlbnRQYWdlKDE0KTtcclxuJHAtPmNoYW5nZUNs
YXNzKCJtZW5lYW1lIik7XHJcbiRwLT5zaG93KCk7PC9jb2RlPlxyXG5cclxuPHA+RWwgc2lndWll
bnRlIGVqZW1wbG86PC9wPlxyXG5cclxuPGNvZGUgbGFuZz0icGhwIj5cclxuJHRvdGFsID0gPD9w
aHAgZWNobyAkdG90YWw/PjtcclxuZm9yKCRjPTE7JGM8PSR0b3RhbDskYysrKXtcclxuCWVjaG8g
IjxwPkxvcmVtIGlwc3VtIGRvbG9yIHNpdCBhbWV0LCBjb25zZWN0ZXR1ciBhZGlwaXNjaW5nIGVs
aXQuIFN1c3BlbmRpc3NlIGV1IGVnZXN0YXMgbGlndWxhLiBOdW5jIHByZXRpdW0gdGluY2lkdW50
IGVsZWlmZW5kLiBOdWxsYW0gc2l0IGFtZXQgcmlzdXMgbGVjdHVzLCBpZCBibGFuZGl0IGxpZ3Vs
YS4gTnVsbGEgc29kYWxlcyBwdWx2aW5hciBsb3JlbSwgcmhvbmN1cyBydXRydW0gbGVjdHVzIGFs
aXF1YW0gcG9ydGEuIFByb2luIGN1cnN1cyBuaXNsIGV1IHF1YW0gYWNjdW1zYW4gcHVsdmluYXIg
YWxpcXVldCB1cm5hIGZlcm1lbnR1bS4gSW50ZWdlciB0aW5jaWR1bnQgbmliaCBzaXQgYW1ldCBu
dW5jIGNvbnNlcXVhdCBzZWQgcG9ydHRpdG9yIGRvbG9yIGxhY2luaWEuIE51bGxhIG5lYyBwdXJ1
cyBlZ2V0IGRpYW0gdGluY2lkdW50IGF1Y3Rvci4gUGVsbGVudGVzcXVlIGF0IGV1aXNtb2Qgb3Jj
aS4gTW9yYmkgbGlndWxhIHJpc3VzLCBncmF2aWRhIGVnZXQgcGxhY2VyYXQgZXUsIGV1aXNtb2Qg
bmVjIG5lcXVlLiBQcmFlc2VudCBub24gbG9yZW0gcHVydXMuIE51bGxhIGZhY2lsaXNpcyBhbnRl
IHV0IHNlbSBmYXVjaWJ1cyBvcm5hcmUuIEV0aWFtIG5lYyBuaXNsIHF1YW0uIEN1bSBzb2NpaXMg
bmF0b3F1ZSBwZW5hdGlidXMgZXQgbWFnbmlzIGRpcyBwYXJ0dXJpZW50IG1vbnRlcywgbmFzY2V0
dXIgcmlkaWN1bHVzIG11cy48L3A+Ijs8ZGVsPjwvZGVsPlxyXG59XHJcbjwvY29kZT5cclxuXHJc
bjxwPkRlc3BsaWVnYSA8P3BocCBlY2hvICR0b3RhbDsgPz4gcMOhcnJhZm9zOiA8L3A+XHJcblxy
XG48P3BocCBmb3IoJGM9MTskYzw9JHRvdGFsOyRjKyspeyA/PlxyXG4JPHA+TG9yZW0gaXBzdW0g
ZG9sb3Igc2l0IGFtZXQsIGNvbnNlY3RldHVyIGFkaXBpc2NpbmcgZWxpdC4gU3VzcGVuZGlzc2Ug
ZXUgZWdlc3RhcyBsaWd1bGEuIE51bmMgcHJldGl1bSB0aW5jaWR1bnQgZWxlaWZlbmQuIE51bGxh
bSBzaXQgYW1ldCByaXN1cyBsZWN0dXMsIGlkIGJsYW5kaXQgbGlndWxhLiBOdWxsYSBzb2RhbGVz
IHB1bHZpbmFyIGxvcmVtLCByaG9uY3VzIHJ1dHJ1bSBsZWN0dXMgYWxpcXVhbSBwb3J0YS4gUHJv
aW4gY3Vyc3VzIG5pc2wgZXUgcXVhbSBhY2N1bXNhbiBwdWx2aW5hciBhbGlxdWV0IHVybmEgZmVy
bWVudHVtLiBJbnRlZ2VyIHRpbmNpZHVudCBuaWJoIHNpdCBhbWV0IG51bmMgY29uc2VxdWF0IHNl
ZCBwb3J0dGl0b3IgZG9sb3IgbGFjaW5pYS4gTnVsbGEgbmVjIHB1cnVzIGVnZXQgZGlhbSB0aW5j
aWR1bnQgYXVjdG9yLiBQZWxsZW50ZXNxdWUgYXQgZXVpc21vZCBvcmNpLiBNb3JiaSBsaWd1bGEg
cmlzdXMsIGdyYXZpZGEgZWdldCBwbGFjZXJhdCBldSwgZXVpc21vZCBuZWMgbmVxdWUuIFByYWVz
ZW50IG5vbiBsb3JlbSBwdXJ1cy4gTnVsbGEgZmFjaWxpc2lzIGFudGUgdXQgc2VtIGZhdWNpYnVz
IG9ybmFyZS4gRXRpYW0gbmVjIG5pc2wgcXVhbS4gQ3VtIHNvY2lpcyBuYXRvcXVlIHBlbmF0aWJ1
cyBldCBtYWduaXMgZGlzIHBhcnR1cmllbnQgbW9udGVzLCBuYXNjZXR1ciByaWRpY3VsdXMgbXVz
LjwvcD5cclxuPD9waHAgfT8+XHJcblxyXG48cD5QYXRoID0gPD9waHAgZWNobyBQYXRoOyA/Pjwv
cD5cclxuXHJcbjxwPkVsIHNpZ3VpZW50ZSBjw7NkaWdvIGRhIGZvbmRvIGFtYXJpbGxvIGEgdW4g
ZWxlbWVudG8gZGUgbGEgbGlzdGEgZGVsIGluaWNpbzwvcD5cclxuXHJcbjxjb2RlIGxhbmc9Imph
dmFzY3JpcHQiPlxyXG4kKGRvY3VtZW50KS5yZWFkeShmdW5jdGlvbigpe1xyXG4JJCgibGkjYm90
b24tY2luY28iKS5jc3MoImJhY2tncm91bmQtY29sb3IiLCJ5ZWxsb3ciKTtcclxufSk7XHJcbjwv
Y29kZT5cclxuXHJcbjxzY3JpcHQ+XHJcbiQoZG9jdW1lbnQpLnJlYWR5KGZ1bmN0aW9uKCl7XHJc
bgkkKCJsaSNib3Rvbi1jaW5jbyIpLmNzcygiYmFja2dyb3VuZC1jb2xvciIsInllbGxvdyIpO1xy
XG59KTtcclxuPC9zY3JpcHQ+JywgJ3B1Ymxpc2gnLCAxLCAwLCAnMjAxMC0wOC0yMyAyMTowNjow
NicsICcyMDEwLTA4LTIzIDIxOjEwOjUwJyk7DQo=";
		
		$M = mysqli_db::getInstance();
		$M->query(base64_decode($sql));
	}
	
	public function delete_posts(){
		$M = mysqli_db::getInstance();

		$sql = "DELETE FROM posts";
		
		$M->query($sql);
	}
	
	public function add_tags(){
	
	}
	
	public function delete_tags(){
	
	}
	
	public function add_comments(){
	
	}
	
	public function delete_comments(){
	
	}
}
