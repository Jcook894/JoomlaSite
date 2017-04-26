<?php 
/** 
 * @package JMAP::PINGOMATIC::administrator::components::com_jmap
 * @subpackage views
 * @subpackage pingomatic
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' ); 
?>
<form action="index.php" method="post" name="adminForm" id="adminForm"> 
	<div id="accordion_pingomatic_details" class="panel panel-info panel-group adminform">
		<div class="panel-heading" data-target="#pingomatic_details"><h4><?php echo JText::_('COM_JMAP_PINGOMATIC_DETAILS' ); ?></h4></div>
		<div class="panel-body panel-collapse collapse" id="pingomatic_details">
			<table class="admintable">
				<tbody>
					<tr>
						<td class="key left_title">
							<label for="title">
								<?php echo JText::_('COM_JMAP_LINKTITLE' ); ?>:
							</label>
						</td>
						<td class="right_details">
							<input type="text" class="inputbox" name="title" id="title" data-validation="required" value="<?php echo $this->record->title;?>" />
						</td>
					</tr>
					<tr>
						<td class="key left_title">
							<label for="type">
								<?php echo JText::_('COM_JMAP_LINKURL' ); ?>:
							</label>
						</td>
						<td class="right_details">
							<input type="text" class="inputbox" name="blogurl" id="linkurl" data-validation="required url" value="<?php echo $this->record->blogurl;?>" />
							<label class="as label label-primary hasClickPopover" data-title="<?php echo JText::_('COM_JMAP_PICKURL_DESC');?>"><?php echo JText::_('COM_JMAP_PICKURL');?></label>
						</td>
					</tr> 
					<tr>
						<td class="key left_title">
							<label for="description">
								<?php echo JText::_('COM_JMAP_RSSURL' ); ?>:
							</label>
						</td>
						<td class="right_details">
							<input type="text" class="inputbox" name="rssurl" id="rssurl" data-validation="url" value="<?php echo $this->record->rssurl;?>" />
						</td>
					</tr> 
					<tr>
						<td class="key left_title">
							<label for="description">
								<?php echo JText::_('COM_JMAP_LASTPING' ); ?>:
							</label>
						</td>
						<td class="right_details">
							<label for="description" id="lastping">
								<?php echo $this->record->lastping; ?>
							</label>
						</td>
					</tr> 
				</tbody>
			</table>
		</div>
	</div>
	
	<div id="accordion_pingomatic_services" class="panel panel-info panel-group adminform">
		<div class="panel-heading" data-target="#pingomatic_services"><h4><?php echo JText::_('COM_JMAP_PINGOMATIC_SERVICES' ); ?></h4></div>
		<div class="panel-body panel-collapse collapse" id="pingomatic_services">	
			<table class="admintable">
				<tbody>
					<tr>
						<td class="key left_title">
							<label for="description">
								<?php echo JText::_('COM_JMAP_SERVICES_LIST' ); ?>:
							</label>
						</td>
						<td class="right_details">
							<div class="panel panel-success">
								<div class="panel-heading">
							  		<?php echo JText::_('COM_JMAP_COMMON_SERVICES'); ?>
							  	</div>
								<div class="panel-body">
								    <div id="common">
								    	<div class="service_control"><span class="controls"><?php echo $this->lists['chk_google'];?></span><a href="http://blogsearch.google.com/" target="_blank"><label class="as label label-info">Google Blog Search</label></a></div>
								    	<div class="service_control"><span class="controls"><?php echo $this->lists['chk_weblogscom'];?></span><a href="http://www.weblogs.com/" target="_blank"><label class="as label label-info">Weblogs.com</label></a></div>
								    	<div class="service_control"><span class="controls"><?php echo $this->lists['chk_blogs'];?></span><a href="http://blo.gs/" target="_blank"><label class="as label label-info">Blo.gs</label></a></div>
								    	<div class="service_control"><span class="controls"><?php echo $this->lists['chk_feedburner'];?></span><a href="http://feedburner.com/" target="_blank"><label class="as label label-info">Feed Burner</label></a></div>
								    	<div class="service_control"><span class="controls"><?php echo $this->lists['chk_newsgator'];?></span><a href="http://www.newsgator.com/" target="_blank"><label class="as label label-info">NewsGator</label></a></div>
								    	<div class="service_control"><span class="controls"><?php echo $this->lists['chk_myyahoo'];?></span><a href="http://my.yahoo.com/" target="_blank"><label class="as label label-info">My Yahoo!</label></a></div>
								    	
								    	<div class="service_control"><span class="controls"><?php echo $this->lists['chk_pubsubcom'];?></span><a href="http://pubsub.com/" target="_blank"><label class="as label label-info">PubSub.com</label></a></div>
								    	<div class="service_control"><span class="controls"><?php echo $this->lists['chk_blogdigger'];?></span><a href="http://blogdigger.com/" target="_blank"><label class="as label label-info">Blogdigger</label></a></div>
								    	<div class="service_control"><span class="controls"><?php echo $this->lists['chk_weblogalot'];?></span><a href="http://www.weblogalot.com/" target="_blank"><label class="as label label-info">Weblogalot</label></a></div>
								    	<div class="service_control"><span class="controls"><?php echo $this->lists['chk_newsisfree'];?></span><a href="http://www.newsisfree.com/" target="_blank"><label class="as label label-info">News Is Free</label></a></div>
								    	<div class="service_control"><span class="controls"><?php echo $this->lists['chk_topicexchange'];?></span><a href="http://topicexchange.com/" target="_blank"><label class="as label label-info">Topic Exchange</label></a></div>
								    	<div class="service_control"><span class="controls"><?php echo $this->lists['chk_tailrank'];?></span><a href="http://spinn3r.com/" target="_blank"><label class="as label label-info">Spinn3r</label></a></div>
								    	<div class="service_control"><span class="controls"><?php echo $this->lists['chk_skygrid'];?></span><a href="http://www.skygrid.com/" target="_blank"><label class="as label label-info">SkyGrid</label></a></div>
								    	<div class="service_control"><span class="controls"><?php echo $this->lists['chk_collecta'];?></span><a href="http://collecta.com/" target="_blank"><label class="as label label-info">Collecta</label></a></div>
								    	<div class="service_control"><span class="controls"><?php echo $this->lists['chk_superfeedr'];?></span><a href="http://superfeedr.com/" target="_blank"><label class="as label label-info">Superfeedr</label></a></div>
									</div>
								</div>
							</div>
							<div class="panel panel-success">
								<div class="panel-heading">
							  		<?php echo JText::_('COM_JMAP_SPECIALIZED_SERVICES');?>
							  	</div>
								<div class="panel-body">
									<div id="specialized">
										<div class="service_control"><span class="controls"><?php echo $this->lists['chk_audioweblogs'];?></span><a href="http://audio.weblogs.com/" target="_blank"><label class="as label label-info">Audio.Weblogs</label></a></div>
										<div class="service_control"><span class="controls"><?php echo $this->lists['chk_rubhub'];?></span><a href="http://www.rubhub.com/" target="_blank"><label class="as label label-info">RubHub</label></a></div>
										<div class="service_control"><span class="controls"><?php echo $this->lists['chk_a2b'];?></span><a href="http://www.a2b.cc/" target="_blank"><label class="as label label-info">A2B GeoLocation</label></a></div>
										<div class="service_control"><span class="controls"><?php echo $this->lists['chk_blogshares'];?></span><a href="http://www.blogshares.com/" target="_blank"><label class="as label label-info">BlogShares</label></a></div>
									</div>
								</div>
							</div>
						</td>
					</tr> 
				</tbody>
			</table>
		</div>
	</div>
		
	<input type="hidden" name="option" value="<?php echo $this->option?>" />
	<input type="hidden" name="id" value="<?php echo $this->record->id; ?>" />
	<input type="hidden" name="task" value="" />
</form>

<iframe src="<?php echo $this->urischeme;?>://pingomatic.com/" id="pingomatic_iframe" name="pingomatic_iframe"></iframe>