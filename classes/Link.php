<?php

require_once('settings.php');

class Link {
	
	function __construct() {
		
	}
	
	public static function getPageLink($resourceName) {
		if (DBAccess::selectQuery("SELECT * FROM articles WHERE src = '$resourceName'") == null) {
			/* generated articles not defined
			$page = DBAccess::selectQuery("SELECT parentId FROM generated_articles WHERE src = '$resourceName'");
			$page = intval($page[0]['parentId']);
			$upper = DBAccess::selectQuery("SELECT * FROM articles WHERE id = $page");
			$upper = $upper[0];
			
			return WEB_URL . SUB_URL . $upper['src'] . "/" . $resourceName;
			*/

			$link = WEB_URL . SUB_URL . "";
			return $link; 
		}
		
		$link = WEB_URL . SUB_URL . $resourceName;
		return $link;
	}

	public static function getFrontOfficeLink($page) {
		if (DBAccess::selectQuery("SELECT * FROM frontpage WHERE src = '$page'") == null) {
			$link = WEB_URL . FRONT . "";
			return $link; 
		}
		
		$link = WEB_URL . FRONT . $page;
		return $link;
	}

	public static function getFrontOfficeName($page) {
		$data = DBAccess::selectQuery("SELECT pageName FROM frontpage WHERE src = '$page'");

		if ($data != null) {
			return $data[0]["pageName"];
		}
			
		return null;
	}
	
	public static function getImageLink($resourceName) {
		$link = REWRITE_BASE . "files/res/image/" . $resourceName;
		return $link;
	}
	
	public static function getResourcesLink($resource, $type, $rewriteBase = true) {
		if($rewriteBase) {
			$rewriteBase = REWRITE_BASE;
		} else {
			$rewriteBase = "";
		}
		switch($type) {
			case "css":
				$link = $rewriteBase . "files/res/css/" . $resource;
				break;
			case "js":
				$link = $rewriteBase . "files/res/js/" . $resource;
				break;
			case "font":
				$link = $rewriteBase . "files/res/font/" . $resource;
				break;
			case "html":
				$link = $rewriteBase . "files/res/form/" . $resource;
				break;
			case "upload":
				$link = $rewriteBase . "upload/" . $resource;
				break;
			case "backup":
				$link = $rewriteBase . "files/generated/sql_backups/" . $resource;
				break;
			case "pdf":
				$link = $rewriteBase . "files/generated/invoice/" . $resource;
				break;
		}
		
		return $link;
	}

	public static function getResourcesShortLink($resource, $type) {
		switch($type) {
			case "css":
				$link = REWRITE_BASE . "css/" . $resource;
				break;
			case "js":
				$link = REWRITE_BASE . "js/" . $resource;
				break;
			case "extJs":
				/* extJs is for external js files, therefoe the fileSrc table column is returned ($resource) */
				$link = $resource;
				break;
			case "font":
				$link = REWRITE_BASE . "font/" . $resource;
				break;
			case "upload":
				$link = REWRITE_BASE . "upload/" . $resource;
				break;
			case "img":
				$link = REWRITE_BASE . "img/" . $resource;
				break;
			case "backup":
				$link = REWRITE_BASE . "backup/" . $resource;
				break;
			case "pdf":
				$link = REWRITE_BASE . "pdf_invoice/" . $resource;
				break;
		}
		
		return $link;
	}
	
	public static function getGlobalCSS() {
		return self::getResourcesShortLink("global.css", "css");
	}
	
	public static function getGlobalJS() {
		return self::getResourcesShortLink("global.js", "js");
	}

	public static function getGlobalFrontCSS() {
		return self::getResourcesShortLink("front/global.css", "css");
	}
	
	public static function getAdminLink() {
		$link = REWRITE_BASE . "admin/";
		return $link;
	}

	public static function getCategoryLink($page) {
        $link = WEB_URL . "/shop/category/" . $page;
		return $link;
    }

	/*
	 * function returns an array of link objects by breaking down the server uri
	 * variable.
	 * the links are representing the depth of the link
	 */
	public static function parseUri() {
		$url = $_SERVER["REQUEST_URI"];

		/* remove GET parameters */
		$url = explode("?", $url)[0];
		
		/* remove WEB_URL and FRONT */
		$url = str_replace(WEB_URL . substr(FRONT, 0, -1), "", $url);

		$url_parts = explode("/", $url);

		$links = array();
		foreach ($url_parts as $u) {
			if (strcmp($u, WEB_URL) != 0) {
				$urlLink = new Link();
				$link = [
					"link" => Link::getFrontOfficeLink($u),
					"text" => Link::getFrontOfficeName($u)
				];
				array_push($links, $link);
			}
		}

		return $links;
	}
	
	public static function generateBreadcrumbList($pageName) {
		$page = DBAccess::selectQuery("SELECT src FROM articles WHERE pageName = '$pageName'");
		if($page == null) {
			$page = DBAccess::selectQuery("SELECT src FROM generated_articles WHERE pageName = '$pageName'");
		}
		
		$page = $page[0];
		
		$pageSrc = $page['src'];
		$isSubCategory = DBAccess::selectQuery("SELECT isSubCatOf FROM categories WHERE name = '$pageSrc'");
		
		if(isset($isSubCategory[0])) {
			$isSubCategory = $isSubCategory[0];
		}
		
		if($isSubCategory == null || $isSubCategory['isSubCatOf'] == "0") {
			echo 
			'<ul class="breadcrumb" itemscope itemtype="http://schema.org/BreadcrumbList">
				<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
					<a itemprop="item" href="' . WEB_URL . '"><span itemprop="name">Home</span></a> ›
				</li>
				<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
					<a itemprop="item" href="' . WEB_URL . self::getPageLink($pageSrc) . '"><span itemprop="name">' . $pageName . '</span></a>
				</li >
			<ul>';
		} else {
			$id = intval($isSubCategory["isSubCatOf"]);
			$upperPage = self::getUpperCat($id);
			
			echo 
			'<ul class="breadcrumb" itemscope itemtype="http://schema.org/BreadcrumbList">
				<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
					<a itemprop="item" href="' . WEB_URL . '"><span itemprop="name">Home</span></a> ›
				</li>
				<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
					<a itemprop="item" href="' . WEB_URL . self::getPageLink($upperPage['src']) . '"><span itemprop="name">' . $upperPage['pageName'] . '</span></a> ›
				</li>
				<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
					<a itemprop="item" href="' . WEB_URL . self::getPageLink($pageSrc) . '"><span itemprop="name">' . $pageName . '</span></a>
				</li >
			<ul>';
		}
	}
	
	private static function getUpperCat($id) {
		$upperPage = DBAccess::selectQuery("SELECT name FROM categories WHERE id = $id");
		$upperPage = $upperPage[0]['name'];
		$upperPage = DBAccess::selectQuery("SELECT * FROM articles WHERE pageName = '$upperPage'");
		if(isset($upperPage[0])) {
			return $upperPage[0];
		}
		
		return $upperPage;
	}

	/* new link functionalities */
	public function addBaseLink($target) {
		$this->baseLink = self::getPageLink($target);
	}

	public function addParameter($key, $value) {
		return $this->baseLink . "?$key=$value";
	}

	public function setIterator($key, $data, $datakey) {
		$this->key = $key;
		$this->data = $data;
		$this->datakey = $datakey;
	}

	public function getLink($id) {
		return self::addParameter($this->key, $this->data[$id][$this->datakey]);
	}
	
}

?>