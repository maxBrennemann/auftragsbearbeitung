<?php

require_once('settings.php');

class Link {
	
	function __construct() {
		
	}
	
	public static function getPageLink($resourceName) {
		if(DBAccess::selectQuery("SELECT * FROM articles WHERE src = '$resourceName'") == null) {
			$page = DBAccess::selectQuery("SELECT parentId FROM generated_articles WHERE src = '$resourceName'");
			$page = intval($page[0]['parentId']);
			$upper = DBAccess::selectQuery("SELECT * FROM articles WHERE id = $page");
			$upper = $upper[0];
			
			return WEB_URL . SUB_URL . $upper['src'] . "/" . $resourceName;
		}
		
		$link = WEB_URL . SUB_URL . $resourceName;
		return $link;
	}
	
	public static function getImageLink($resourceName) {
		$link = REWRITE_BASE . "files/res/img/" . $resourceName;
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
			case "font":
				$link = REWRITE_BASE . "font/" . $resource;
				break;
			case "upload":
				$link = REWRITE_BASE . "upload/" . $resource;
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
	
	public static function getAdminLink() {
		$link = REWRITE_BASE . "admin/";
		return $link;
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
	
}

?>