<?php
defined('IN_PHPCMS') or exit('No permission resources.');
//模型缓存路径
define('CACHE_MODEL_PATH',CACHE_PATH.'caches_model'.DIRECTORY_SEPARATOR.'caches_data'.DIRECTORY_SEPARATOR);
class new_index {
	private $db;
	function __construct() {
		$this->db = pc_base::load_model('content_model');
	}
	//新闻首页
	public function init() {
		$cat_db = pc_base::load_model('category_model');
		$SEO = seo(1,20);//后台SEO调用
		$catid = isset($_GET['catid']) ? trim($_GET['catid']) : 20;
		$catdir = isset($_GET['catdir']) ? trim($_GET['catdir']):'';
		$catdirs = $cat_db->get_one(array('catdir'=>$catdir),'catid');
		$catids = $catdirs['catid'] ? $catdirs['catid'] : $catid;
		$page = isset($_GET['page']) ? intval($_GET['page']):1;
		$pagesize = 6;
		$offset = $pagesize * ($page - 1);
		$catchild = $cat_db->listinfo(array('parentid'=>20),'listorder ASC');//获取新闻中心下的栏目
		$catinfo = $cat_db->get_one(array('catid'=>$catids),'arrparentid,arrchildid,catname');
		if(!$catinfo['arrparentid']){//判断是否为一级栏目（是）
			/* $cat = array();
			$cat = explode(',', $catinfo['arrchildid']);
			$catids = $cat[1];		//默认取该一级栏目下的第一个栏目 */
			$catids = '';
		}
		$pre = $this->db->db_tablepre;
		$where = ' WHERE status=99';
		if($catids){
			$where .= ' AND catid=' .$catids;
		}
		$sql = 'SELECT count(*) num FROM ' .$pre. 'news' .$where;
		$res = $this->db->query($sql);
		$aCount = $this->db->fetch_next();
		if(!$catinfo['arrparentid']){
			$urlrulesId = 35;
		}else{
			$urlrulesId = 38;
		}
		//URL规则
		if ($urlrulesId) {
			$urlrules = getcache('urlrules', 'commons');
			$urlrules = str_replace('|', '~', $urlrules [$urlrulesId]);
			$tmp_urls = explode('~', $urlrules);
			$tmp_urls = isset($tmp_urls[1]) ? $tmp_urls[1] : $tmp_urls[0];
			preg_match_all('/{\$([a-z0-9_]+)}/i', $tmp_urls, $_urls);
			if (!empty($_urls[1])) {
				foreach($_urls[1] as $_v) {
					$GLOBALS['URL_ARRAY'][$_v] = $$_v;
				}
			}
			define('URLRULE', $urlrules);
		}
		$pages = pages ($aCount['num'], $page, $pagesize);//分页
		$totalPage = ceil ($aCount['num'] / $pagesize);
		
		$sql = 'SELECT * FROM ' .$pre. 'news' .$where. ' ORDER BY listorder DESC, id DESC LIMIT ' .$offset. ',' .$pagesize;
		$res = $this->db->query($sql);
		$newsinfo = array();
		while($r = $this->db->fetch_next()){
			$newsinfo[$r['id']] = $r;
		}				
		include template('umx','new');
	}
	
	//资讯详情
	public function news_detail() {
		$sites = getcache('sitelist', 'commons');
		$catid = 20;
		$id = isset($_GET['id']) ? trim($_GET['id']):'';
		if(!$id || !intval($id)){
			showmessage('参数错误',HTTP_REFERER);
		}
		$pre = $this->db->db_tablepre;
		$where = ' WHERE n.id='.$id;
		$sql = 'SELECT n.*, nd.content, nd.author FROM ' .$pre. 'news n LEFT JOIN ' .$pre. 'news_data nd ON n.id=nd.id' .$where;
		$res = $this->db->query($sql);
		$newsinfo = $this->db->fetch_next();
		if(empty($newsinfo)){
			$SEO['title'] = '404错误-优美迅|优美迅科技';
			$SEO['keyword'] = '404,404错误,优美迅';
			$SEO['description'] = '404错误,您要查看的网址可能已被删、名称已被更改，或者暂时不可用';
			include template('umx','404');exit;
		}
		
		$cat_db = pc_base::load_model('category_model');
		$catinfo = $cat_db->get_one(array('catid'=>$newsinfo['catid']),'catname,url');
		
		$SEO['title'] = $newsinfo['title'].'-'.$sites[1]['site_title'];
		$SEO['keyword'] = $newsinfo['keywords'];
		$SEO['description'] = $newsinfo['description'];
		
		include template('umx','new_detail');
	}
}
?>