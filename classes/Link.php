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
			
			return WEB_URL . REWRITE_BASE . "content/" . $upper['src'] . "/" . $resourceName;
		}
		
		$link = WEB_URL . REWRITE_BASE . "content/" . $resourceName;
		return $link;
	}
	
	public static function getImageLink($resourceName) {
		$link = REWRITE_BASE . "files/img/" . $resourceName;
		return $link;
	}
	
	public static function getResourcesLink($resource, $type) {
		switch($type) {
			case "css":
				$link = REWRITE_BASE . "files/css/" . $resource;
				break;
			case "js":
				$link = REWRITE_BASE . "files/js/" . $resource;
				break;
			case "font":
				$link = REWRITE_BASE . "files/font/" . $resource;
				break;
		}
		
		return $link;
	}
	
	public static function getGlobalCSS() {
		$link = REWRITE_BASE . "files/css/global.css";
		return $link;
	}
	
	public static function getGlobalJS() {
		$link = REWRITE_BASE . "files/js/global.js";
		return $link;
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