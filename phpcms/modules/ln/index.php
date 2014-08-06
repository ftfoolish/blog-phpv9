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
	
	//关于我们
	public function about() {
		$SEO = seo(1,18);
		$catid = isset($_GET['catid'])?trim(intval($_GET['catid'])):'';
		
		$catid = $catid?$catid:'18';
		
		$page_db = pc_base::load_model('page_model');
		$r = $page_db->get_one(array('catid'=>$catid));
		include template('umx','about');
	}
	
	//联系我们
	public function contact() {
		$SEO = seo(1,19);
		$catid = isset($_GET['catid']) ? trim(intval($_GET['catid'])):'';
	
		$catid = $catid ? $catid:'19';
	
		$page_db = pc_base::load_model('page_model');
		$r = $page_db->get_one(array('catid'=>$catid));
		include template('umx','contact');
	}
	
	//业务需求
	public function msg(){
		$demand_db = pc_base::load_model('demand_model');
		$this->_session_start();
		if ($_SESSION['code'] != strtolower($_POST['code']) || empty($_SESSION['code'])) {
			exit('0');
		} else {
			$_SESSION['code'] = '';
		}
		$info = array(
			'name' => trim($_POST['name']),
			'mobile' => trim($_POST['phone']),
			'email' => trim($_POST['email']),
			'content' => trim($_POST['content']),
			'inputtime' => SYS_TIME, 
    		'ip' => ip(), 
		);
		$id = $demand_db->insert($info);
		if ($id) {
			exit('1');
		} else {
			exit('-1');
		}
	}
	
	private function _session_start() {
		$session_storage = 'session_'.pc_base::load_config('system','session_storage');
		pc_base::load_sys_class($session_storage);
	}
}
?>