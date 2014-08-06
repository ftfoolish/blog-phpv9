<?php
defined('IN_PHPCMS') or exit('No permission resources.');
//模型缓存路径
define('CACHE_MODEL_PATH',CACHE_PATH.'caches_model'.DIRECTORY_SEPARATOR.'caches_data'.DIRECTORY_SEPARATOR);
class index {
	private $db;
	function __construct() {
		$this->db = pc_base::load_model('content_model');
		$this->_userid = param::get_cookie('_userid');
		$this->_username = param::get_cookie('_username');
		$this->_groupid = param::get_cookie('_groupid');
	}
	//首页
	public function init() {
		$cat_db = pc_base::load_model('category_model');
		$SEO = seo(1,9);//后台SEO调用
		$catid = isset($_GET['catid']) ? trim($_GET['catid']) : 9;
		$page = isset($_GET['page']) ? intval($_GET['page']):1;
		$pagesize = 6;
		$offset = $pagesize * ($page - 1);
		$pre = $this->db->db_tablepre;
		$where = ' WHERE status=99';
		if($catid != 9){
			$where .= ' AND catid=' .$catid;
		}
		$sql = 'SELECT count(*) num FROM ' .$pre. 'news' .$where;
		$res = $this->db->query($sql);
		$aCount = $this->db->fetch_next();
		$pages = pages ($aCount['num'], $page, $pagesize);//分页
		$totalPage = ceil ($aCount['num'] / $pagesize);
		$sql = 'SELECT * FROM ' .$pre. 'news' .$where. ' ORDER BY listorder DESC, id DESC LIMIT ' .$offset. ',' .$pagesize;
		$res = $this->db->query($sql);
		$newsinfo = array();
		while($r = $this->db->fetch_next()){
			$newsinfo[$r['id']] = $r;
		}		
		$catchild = $cat_db->listinfo(array('parentid'=>9),'listorder ASC');//获取新闻中心下的栏目
		include template('ln','new');
	}
	
//资讯详情
	public function detail() {
		$sites = getcache('sitelist', 'commons');
		$catid = 9;
		$id = isset($_GET['id']) ? trim($_GET['id']):'';
		if(!$id || !intval($id)){
			showmessage('参数错误',HTTP_REFERER);
		}
		$pre = $this->db->db_tablepre;
		$where = ' WHERE n.id='.$id;
		$sql = 'SELECT n.*, nd.content FROM ' .$pre. 'news n LEFT JOIN ' .$pre. 'news_data nd ON n.id=nd.id' .$where;
		$res = $this->db->query($sql);
		$newsinfo = $this->db->fetch_next();
		if(empty($newsinfo)){
			$SEO['title'] = '404错误-老牛笔记';
			$SEO['keyword'] = '404,404错误,老牛笔记';
			$SEO['description'] = '404错误,您要查看的网址可能已被删、名称已被更改，或者暂时不可用';
			include template('ln','404');exit;
		}
		
		$cat_db = pc_base::load_model('category_model');
		$catinfo = $cat_db->get_one(array('catid'=>$newsinfo['catid']),'catname,url');
		
		$SEO['title'] = $newsinfo['title'].'-'.$sites[1]['site_title'];
		$SEO['keyword'] = $newsinfo['keywords'];
		$SEO['description'] = $newsinfo['description'];
		
		include template('ln','new_detail');
	}
	
	private function _session_start() {
		$session_storage = 'session_'.pc_base::load_config('system','session_storage');
		pc_base::load_sys_class($session_storage);
	}
}
?>