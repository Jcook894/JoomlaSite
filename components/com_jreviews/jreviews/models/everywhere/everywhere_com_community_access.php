<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2010-2012 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class EverywhereComCommunityAccessModel extends MyModel  {
			
	var $UI_name = 'JomSocial - Access Groups';
		
	/**
	 * Create a field with the name shown below, or change it's value to an existing field name.
	 * The options of this field will be used as categories
	 * Make sure this is a required field and shown at registration time
	 */
	var $cbCustomField = 'FIELD_MEMBERTYPE';

	var $name = 'Listing';
	
	var $useTable = '#__users AS Listing';
	
	var $primaryKey = 'Listing.listing_id';
	
	var $realKey = 'id';
	
	/**
	 * Used for listing module - latest listings ordering
	 */	
	var $dateKey = 'registerDate';	
	
	/**
	 * This is the component's url option parameter
	 *
	 * @var string
	 */
	var $extension = 'com_community';
	
	/**
	 * This is the value stored in the reviews table to differentiate the source of the reviews
	 *
	 * @var string
	 */
	var $extension_alias = 'com_community_access';

	var $listingUrl = 'index.php?option=com_community&view=profile&userid=%s&Itemid=%s';	
	
	var $categoryPrimaryKey = 'id';
		
	var $fields = array(
		'Listing.id AS `Listing.listing_id`',
		'Listing.title'=>'Listing.username AS `Listing.title`',
		'Community.thumb AS `Listing.images`', 
		"'com_community_access' AS `Listing.extension`",
		'JreviewsCategory.id AS `Listing.cat_id`',
		'cat_name'=>'Category.name AS `Category.title`',
		'JreviewsCategory.id AS `Category.cat_id`',
		'Criteria.id AS `Criteria.criteria_id`',
		'Criteria.criteria AS `Criteria.criteria`',
		'Criteria.tooltips AS `Criteria.tooltips`',
		'Criteria.weights AS `Criteria.weights`',
        'Criteria.required AS `Criteria.required`',       
		'Criteria.state AS `Criteria.state`',
        'Criteria.config AS `ListingType.config`',
        'User.id AS `User.user_id`',
        'User.name AS `User.name`',
        'User.username AS `User.username`',
        'User.email AS `User.email`',
        // User reviews
        'user_rating'=>'Totals.user_rating AS `Review.user_rating`',
        'Totals.user_rating_count AS `Review.user_rating_count`',
        'Totals.user_criteria_rating AS `Review.user_criteria_rating`',
        'Totals.user_criteria_rating_count AS `Review.user_criteria_rating_count`',
        'Totals.user_comment_count AS `Review.review_count`'
    );
	
	// Done in the __construct function below because it's CMS dependent for categoryPrimaryKey
	var $joins = array();
	
	var $joinsReviews = array();
	
    var $groups_table = array();
    
    var $groups_title_col;
    
	// Module controller includes the basic joins for review and rating information and others can be added here
	// depending on the fields used in the query
	var $joinsListingsModule = array();		
		
	function __construct() {
		
		parent::__construct();
		
        $this->groups_table = $this->cmsVersion == CMS_JOOMLA15 ? '#__core_acl_aro_groups' : '#__usergroups';    
        $this->groups_title_col = $this->cmsVersion == CMS_JOOMLA15 ? 'name' : 'title';    
        
        $this->fields['cat_name'] = "Category.{$this->groups_title_col} AS `Category.title`";
        
        if($this->cmsVersion == CMS_JOOMLA15)
        {
            $this->joins = array(
                'Total'=>"LEFT JOIN #__jreviews_listing_totals AS Totals ON Totals.listing_id = Listing.id AND Totals.extension = '{$this->extension_alias}'",
                "LEFT JOIN {$this->groups_table} AS Category ON Listing.gid = Category.{$this->categoryPrimaryKey}",
                "INNER JOIN #__jreviews_categories AS JreviewsCategory ON Listing.gid = JreviewsCategory.id AND JreviewsCategory.`option` = '{$this->extension_alias}'",
                'LEFT JOIN #__jreviews_criteria AS Criteria ON JreviewsCategory.criteriaid = Criteria.id',
                'LEFT JOIN #__community_users AS Community ON Listing.id = Community.userid',
                "LEFT JOIN #__users AS User ON User.id = Listing.id"                       
            );
            
            $this->joinsReviews = array(
                "LEFT JOIN #__users AS Listing ON Review.pid = Listing.id",
                "LEFT JOIN {$this->groups_table} AS Category ON Listing.gid = Category.{$this->categoryPrimaryKey}",
                "INNER JOIN #__jreviews_categories AS JreviewsCategory ON Listing.gid = JreviewsCategory.id AND JreviewsCategory.`option` = '{$this->extension_alias}'",
                'LEFT JOIN #__jreviews_criteria AS Criteria ON JreviewsCategory.criteriaid = Criteria.id'
            );
        }
        else {
            $this->joins = array(
                'Total'=>"LEFT JOIN #__jreviews_listing_totals AS Totals ON Totals.listing_id = Listing.id AND Totals.extension = '{$this->extension_alias}'",
                "INNER JOIN #__user_usergroup_map AS UserGroupMap ON UserGroupMap.user_id = Listing.id",
                "LEFT JOIN {$this->groups_table} AS Category ON UserGroupMap.group_id = Category.{$this->categoryPrimaryKey}",
                "INNER JOIN #__jreviews_categories AS JreviewsCategory ON Category.{$this->categoryPrimaryKey} = JreviewsCategory.id AND JreviewsCategory.`option` = '{$this->extension_alias}'",
                'LEFT JOIN #__jreviews_criteria AS Criteria ON JreviewsCategory.criteriaid = Criteria.id',
                'LEFT JOIN #__community_users AS Community ON Listing.id = Community.userid',
                "LEFT JOIN #__users AS User ON User.id = Listing.id"                       
            );
            
            $this->joinsReviews = array(
                "LEFT JOIN #__users AS Listing ON Review.pid = Listing.id",
                "INNER JOIN #__user_usergroup_map AS UserGroupMap ON UserGroupMap.user_id = Listing.id",
                "LEFT JOIN {$this->groups_table} AS Category ON UserGroupMap.group_id = Category.{$this->categoryPrimaryKey}",
                "INNER JOIN #__jreviews_categories AS JreviewsCategory ON Category.{$this->categoryPrimaryKey} = JreviewsCategory.id AND JreviewsCategory.`option` = '{$this->extension_alias}'",
                'LEFT JOIN #__jreviews_criteria AS Criteria ON JreviewsCategory.criteriaid = Criteria.id'
            );
        }    

		$this->group[] = "Listing.id";
        
        $this->tag = __t("JOMSOCIAL_TAG",true);  // Used in MyReviews page to differentiate from other component reviews
		
		$this->fields[] = "'{$this->tag }' AS `Listing.tag`";		
        
        # Use name or username based on JReviews config
        $Config = Configure::read('JreviewsSystem.Config');
        if($Config->name_choice == 'realname') {
            $this->fields['Listing.title'] = 'Listing.name AS `Listing.title`';
        }
        unset($Config);
	}
	
	function exists() {
		return (bool) @ file_exists(PATH_ROOT . 'components' . _DS . 'com_community' . _DS . 'community.php');
	}		
	
	function listingUrl($listing) {
		return sprintf($this->listingUrl,$listing['Listing']['listing_id'],$listing['Listing']['menu_id']); 
	}
    
    // Used to check whether reviews can be posted by listing owners, owner replies
    function getListingOwner($result_id) {
        $query = "SELECT User.id user_id, User.name, User.email 
            FROM #__users AS User ".
            "WHERE User.id = " . (int) $result_id;
        $this->_db->setQuery($query);
        return current($this->_db->loadAssocList());
    }
   
	function afterFind($results) {

        if (empty($results)) 
        {
            return $results;
        }
		
        # Find Itemid for component
        $Menu = ClassRegistry::getClass('MenuModel');
        
        $menu_id = $Menu->getComponentMenuId('com_community&view=frontpage');
        
        if(!$menu_id)
        {
            $menu_id = $Menu->getComponentMenuId('com_community&view=profile');
        }

        if(!$menu_id)
        {
            $menu_id = $Menu->getComponentMenuId('com_community');
        }
        	
		foreach($results AS $key=>$result) 
		{	
			// Process component menu id
			$results[$key][$this->name]['menu_id'] = $menu_id;
			
			// Process listing url
			$results[$key][$this->name]['url'] = $this->listingUrl($results[$key]);
			
			// Process criteria
			if(isset($result['Criteria']['criteria']) && $result['Criteria']['criteria'] != '') {
				$results[$key]['Criteria']['criteria'] = explode("\n",$result['Criteria']['criteria']);
			}

			if(isset($result['Criteria']['tooltips']) && $result['Criteria']['tooltips'] != '') {
				$results[$key]['Criteria']['tooltips'] = explode("\n",$result['Criteria']['tooltips']);
			}

			if(isset($result['Criteria']['weights']) && $result['Criteria']['weights'] != '') {
				$results[$key]['Criteria']['weights'] = explode("\n",$result['Criteria']['weights']);
			}
			
			// Process images	
			$images = $result['Listing']['images'];

			unset($results[$key]['Listing']['images']);
			
			$results[$key]['Listing']['images'] = array();

            $imagePath = $images != '' ? $images : 'components/com_community/assets/user_thumb.png';    

			$results[$key]['Listing']['images'][] = array(
				'path'=>$imagePath,
				'caption'=>$results[$key]['Listing']['title'],
				'basepath'=>true,
				'skipthumb'=>$images != '' ? false : true
			);	

		}

		return $results;
	}	
	
	/**
	 * This can be used to add post review save actions, like synching with another table
	 *
	 * @param array $model
	 */
	function afterSave(&$model) {
//		appLogMessage(print_r($model,true),'plgAfterSave');
//		switch($model->name)  {
//			case 'Review':break;
//			case 'Listing':break;			
//		}
	}	
	
	/**
	 * GROUPS MODE STARTS HERE
	 */	
	function getNewCategories()
	{	
		$query = "SELECT id FROM #__jreviews_categories WHERE `option` = '{$this->extension_alias}'";
		$this->_db->setQuery($query);
		$exclude = $this->_db->loadResultArray();
		appLogMessage("getNewCategories\n".$this->_db->getErrorMsg(),'everywhere');		
		
		$exclude = $exclude ? implode(',',$exclude) : '';
			
        $query = "
            SELECT 
                Component.{$this->categoryPrimaryKey} AS value, Component.{$this->groups_title_col} as text
		    FROM 
                {$this->groups_table} AS Component
		    LEFT JOIN 
                #__jreviews_categories AS JreviewCategory ON Component.{$this->categoryPrimaryKey} = JreviewCategory.id AND JreviewCategory.`option` = '{$this->extension_alias}'
		" . ($exclude != '' ? "
                WHERE 
                    Component.{$this->categoryPrimaryKey} NOT IN ($exclude)" : ''
        ) . "
		    ORDER BY 
                Component.{$this->groups_title_col} ASC
        ";
		
		$this->_db->setQuery($query);
		appLogMessage("getNewCategories\n".$this->_db->getQuery(),'everywhere');

		$results = $this->_db->loadAssocList();
		
        if(!empty($results))
        {
            foreach($results AS $key=>$value){
                if(in_array($value['text'],array('Public Backend','Public Frontend','ROOT','USERS'))){
                    unset($results[$key]);
                }
            }
        }

		appLogMessage($this->_db->getErrorMsg(),'everywhere');

		return $results;
	}
		
	function getUsedCategories() 
	{		
		$query = "
            SELECT 
                Component.{$this->categoryPrimaryKey} AS `Component.cat_id`,Component.{$this->groups_title_col} as `Component.cat_title`, Criteria.title AS `Component.criteria_title`
		    FROM 
                {$this->groups_table} AS Component
		    INNER JOIN 
                #__jreviews_categories AS JreviewCategory ON Component.{$this->categoryPrimaryKey} = JreviewCategory.id AND JreviewCategory.`option` = '{$this->extension_alias}'
		    LEFT 
                JOIN #__jreviews_criteria AS Criteria ON JreviewCategory.criteriaid = Criteria.id
		    LIMIT 
                {$this->offset},{$this->limit}
        ";
		$this->_db->setQuery($query);
		appLogMessage("getUsedCategories\n".$this->_db->getQuery(),'everywhere');

		$results = $this->_db->loadObjectList();
		appLogMessage($this->_db->getErrorMsg(),'everywhere');
		
		$results = $this->__reformatArray($results);
		$results = $this->changeKeys($results,'Component','cat_id');
				
		$query = "SELECT count(JreviewCategory.id)"
		. "\n FROM #__jreviews_categories AS JreviewCategory"
		. "\n WHERE JreviewCategory.`option` = '{$this->extension_alias}'"
		;
		$this->_db->setQuery($query);		
				
		$count = $this->_db->loadResult();  

		return array('rows'=>$results,'count'=>$count);
	}
}