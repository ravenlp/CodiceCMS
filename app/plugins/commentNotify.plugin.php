<?php
class commentNotify extends plugins{
	
	private $cookie;
	
	public function __construct(){
		parent::__construct();

		$this->cookie = cookie::getInstance();
	
		if (!defined('GESHI_VERSION') and file_exists(Absolute_Path."app/plugins/geshi/GeSHi.php")) {
			require_once(Absolute_Path."app/plugins/geshi/GeSHi.php");
		}

		$this->addAction('index_comment_added', 'usersNotify');
		$this->addAction('index_comment_added', 'adminNotify');

		$this->addAction('comment_approbed', 'notify');
	}
	
	public function notify(){
		$lastCommentID = $this->registry->lastCommentID;
		
		$Comment = new comment();
		$Comment->find($lastCommentID);		
		
		if($Comment['type'] == 'pingback' OR $Comment['type'] == 'trackback'){
			return;
		}
		
		$this->registry->postID = $Comment['ID_post'];
		$this->usersNotify();
	}
	
	public function usersNotify(){
		$postID = $this->registry->postID;
		$lastCommentID = $this->registry->lastCommentID;
		
		$Comment = new comment();
		$comment = $Comment->find($lastCommentID);
		
		$Post = new post();
		$post = $Post->find($postID);		
		
		if($comment['status'] != 'publish'){
			return;
		}
		
		$Comment = new comment();
		$usersToNotify = $Comment->findAll("email,author,created,ID,content","ID DESC",null,"WHERE suscribe = 1 AND NOT user_id = 1 AND status = 'publish' AND id_post = $postID GROUP BY email");
		
		if(!$usersToNotify){
			return;
		}
		
		$mailStr = "
			<table width=\"100%\">
			<tr>
				<td>
					Hay un nuevo comentario en la entrada \"<a href=\"{$this->registry->path}{$post['urlfriendly']}#comments\">{$post['title']}</a>\".<br />
					<small><a href=\"{$this->registry->path}{$post['urlfriendly']}#comments\">{$this->registry->path}{$post['urlfriendly']}</a></small><br /><br />
				
					<strong>Author</strong>: {$comment['author']}<br />
					<strong>Comentario</strong>: <br />
					{$comment['content']}
				<hr />
			</td></tr>
			<tr>
				<td>
					<p>
						Lee aquí todos los comentarios de esta entrada:<br />
						<a href=\"{$this->registry->path}{$post['urlfriendly']}#comments\">{$this->registry->path}{$post['urlfriendly']}</a><br />
					</p>
				</td>
			</tr>
			</table>
		";
		
		$conf = $this->registry->conf;
		$subject = "[{$conf['blog_name']}] haz recibido respueta a tu comentario en: {$post['title']}";	

		$suscribers = array();
		foreach($usersToNotify as $mail){
			$this->enviaMail($mail['email'],$subject,$mailStr,null);
		}
	}
	
	public function adminNotify(){	
		if($this->cookie->check("id_user") and $this->cookie->id_user == 1){
			//no notificamos a administrador de su propio comentario.
			return;
		}
	
		$id = $this->registry->lastCommentID;

		$Comment = new comment();
		$comment = $Comment->find($id);

		$comment['content'] = utils::nl2br($comment['content']);		
		
		if(!defined('GESHI_VERSION')){
			$comment['content'] = $this->comment_source_code_beautifier($comment['content'],'addTagPRE');
		}else{
			$comment['content'] = $this->comment_source_code_beautifier($comment['content']);
		}
		
		$User = new user();
		$user = $User->find(1);
		
		$Post = new post();
		$post = $Post->find($comment['ID_post']);
		
		$commentsWaiting = $Comment->countCommentsByPost(null,'waiting');
		
		$mailStr = "
			<table width=\"100%\">
			<tr>
				<td>
					<small>
						<strong>From IP</strong>: {$comment['IP']}<br />
						<strong>URL</strong>: <a href=\"{$comment['url']}\">{$comment['url']}</a><br />
						<strong>Email</strong>: <a href=\"mailto:{$comment['email']}\">{$comment['email']}</a><br />
						<strong>DateTime</strong>: {$comment['created']}<br />
					</small>
					<hr>
					<strong>Author</strong>: {$comment['author']}<br />
				</td>
			</tr>
			
			<tr><td><strong>Content</strong></td></tr>
			<tr><td bgcolor=\"#f7f7f7\">
				{$comment['content']}
				<hr />
			</td></tr>
			
			<tr>
				<td>
					<p>
						Moderate comment: <a href=\"{$this->registry->path}comments/edit/{$comment['ID']}\">{$this->registry->path}comments/edit/{$comment['ID']}</a><br />
						View entry: <a href=\"{$this->registry->path}{$post['urlfriendly']}\">{$this->registry->path}{$post['urlfriendly']}</a>
					</p>
		
					<p>
						There are $commentsWaiting comments waiting for approbal. <br />
						Please moderate comments: <a href=\"{$this->registry->path}comments/waiting\">{$this->registry->path}comments</a>
					</p>
				</td>
			</tr>
			
			</table>
		";
		
		$conf = $this->registry->conf;
		$subject = "[{$conf['blog_name']}] Nuevo Comentario en: {$post['title']}";
				
		$this->enviaMail($user['email'],$subject,$mailStr,$user['email']);
	}
	
	private function enviaMail($to, $title, $body, $from = null) {
		$rp     = trim($from);
		$org    = " Codice CMS";
		$mailer = "Codice CMS Mailer";

		$head   = '';
		$head  .= "Content-Type: text/html \r\n";
		$head  .= "Date: ". date('r'). " \r\n";
		$head  .= "Return-Path: $rp \r\n";
		if(!is_null($from))
			$head  .= "From: $from \r\n";
		$head  .= "Sender: $from \r\n";
		$head  .= "Reply-To: $from \r\n";
		$head  .= "Organization: $org \r\n";
		$head  .= "X-Sender: $from \r\n";
		$head  .= "X-Priority: 3 \r\n";
		$head  .= "X-Mailer: $mailer \r\n";

		@mail($to, $title, $body, $head);
		
		return true;
	}
	
	private function comment_source_code_beautifier($comment,$options = null) {
		if($options == 'addTagPRE'){
			$result = preg_replace_callback("/<code\s+.*lang\s*=\"(.*)\">(.*)<\/code>/siU", array('commentNotify', 'replace_with_pre'), $comment);
		}else{
			$result = preg_replace_callback("/<code\s+.*lang\s*=\"(.*)\">(.*)<\/code>/siU", array('commentNotify', 'replace_with_geshi'), $comment);
		}

		return $result;
	}

	private function replace_with_pre($matches) {
		return "<strong>{$matches[1]}</strong> Code: <pre>".htmlspecialchars($matches[2])."</pre>";	
	}

	private function replace_with_geshi($matches) {
		$lang = strtolower($matches[1]);
		$code = trim($matches[2]);
		$geshi = new geshi($code, (isset($lang)) ? $lang : "");    
		$geshi->enable_classes(false);
		$geshi->set_overall_id('geshi_code');
		return @$geshi->parse_code();
	}
	
}
