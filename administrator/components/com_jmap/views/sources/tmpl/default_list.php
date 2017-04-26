<?php 
/** 
 * @package JMAP::SOURCES::administrator::components::com_jmap
 * @subpackage views
 * @subpackage sources
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' ); 
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<table class="headerlist">
		<tr>
			<td align="left" width="100%">
				<span class="input-group">
				  <span class="input-group-addon"><span class="glyphicon glyphicon-filter"></span> <?php echo JText::_('COM_JMAP_FILTER' ); ?>:</span>
				  <input type="text" name="search" id="search" size="60" value="<?php echo htmlspecialchars($this->searchword, ENT_COMPAT, 'UTF-8');?>" class="text_area"/>
				</span>

				<button class="btn btn-primary btn-xs" onclick="this.form.submit();"><?php echo JText::_('COM_JMAP_GO' ); ?></button>
				<button class="btn btn-primary btn-xs" onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_('COM_JMAP_RESET' ); ?></button>
			</td>
			<td nowrap="nowrap">
				<?php
				echo $this->lists['state'];
				echo $this->lists['type'];
				?>
			</td>
		</tr>
	</table>

	<table class="adminlist table table-striped table-hover">
	<thead>
		<tr>
			<th width="20">
				<?php echo JText::_('COM_JMAP_NUM' ); ?>
			</th>
			<th width="20">
				<input type="checkbox" name="toggle" value=""  onclick="checkAll(<?php echo count( $this->items ); ?>);" />
			</th>
			<th nowrap="nowrap" class="title">
				<?php echo JHTML::_('grid.sort',  'COM_JMAP_NAME', 's.name', @$this->orders['order_Dir'], @$this->orders['order'] ); ?>
			</th>
			<th nowrap="nowrap" class="title">
				<?php echo JHTML::_('grid.sort',  'COM_JMAP_TYPE', 's.type', @$this->orders['order_Dir'], @$this->orders['order'] ); ?>
			</th>
			<th nowrap="nowrap" class="title">
				<?php echo JText::_('COM_JMAP_DESCRIPTION'); ?>
			</th>
			<th width="8%">
				<?php echo JHTML::_('grid.sort',   'COM_JMAP_ORDER', 's.ordering', @$this->orders['order_Dir'], @$this->orders['order'] ); ?>
				<?php 
					if(isset($this->orders['order']) && $this->orders['order'] == 's.ordering'):
						echo JHTML::_('grid.order',  $this->items, 'filesave.png', 'sources.saveOrder'); 
					endif;
				 ?>
			</th>
			<th width="5%" nowrap="nowrap">
				<?php echo JHTML::_('grid.sort',   'COM_JMAP_PUBLISHED', 's.published', @$this->orders['order_Dir'], @$this->orders['order'] ); ?>
			</th>
			<th width="1%" nowrap="nowrap">
				<?php echo JHTML::_('grid.sort',   'COM_JMAP_ID', 's.id', @$this->orders['order_Dir'], @$this->orders['order'] ); ?>
			</th>
		</tr>
	</thead>
	<?php
	$k = 0;
	for ($i=0, $n=count( $this->items ); $i < $n; $i++) {
		$row = $this->items[$i];
		$link =  'index.php?option=com_jmap&task=sources.editEntity&cid[]='. $row->id ;

		if($this->user->authorise('core.edit.state', 'com_jmap')) {
			$published = JHTML::_('grid.published', $row, $i, 'tick.png', 'publish_x.png', 'sources.' );
		} else {
			$published = $row->published ? JHtml::image('admin/tick.png', 'unpublish', '', true) : JHtml::image('admin/publish_x.png', 'publish', '', true);
		}
		
		// Check for the links type data source language
		if($row->type == 'links' || $row->type == 'user') {
			$dataSourceParams = json_decode($row->params);
			if(isset($dataSourceParams->datasource_language) && $dataSourceParams->datasource_language != '*') {
				$published .= '<img style="margin-left:10px" src="' . JUri::root(false) . 'media/mod_languages/images/' . $dataSourceParams->datasource_language . '.gif" alt="language_flag" />';
			}
		}
		
		$checked = null;
		if($row->type == 'user' || $row->type == 'plugin' || $row->type == 'links' || ($row->type == 'content' && $this->cParams->get('multiple_content_sources', 0))) {
			if($this->user->authorise('core.edit', 'com_jmap')) {
				$checked = $row->checked_out && $row->checked_out != $this->user->id ? JHtml::_('jgrid.checkedout', $i, '', $row->checked_out_time, 'sources.', false) : JHtml::_('grid.id', $i, $row->id);
			} else {
				$checked = '<input type="checkbox" style="display:none" data-enabled="false" id="cb' . $i . '" name="cid[]" value="' . $row->id . '"/>';
			}
		} else {
			$checked = '<input type="checkbox" style="display:none" data-enabled="false" id="cb' . $i . '" name="cid[]" value="' . $row->id . '"/>';
		}
		?>
		<tr>
			<td align="center">
				<?php echo $this->pagination->getRowOffset($i); ?>
			</td>
			<td align="center">
				<?php echo $checked; ?>
			</td>
			<td>
				<?php
				if ( ($row->checked_out && ( $row->checked_out != $this->user->get ('id'))) || !$this->user->authorise('core.edit', 'com_jmap') ) {
					echo $row->name;
				} else {
					?>
					<a href="<?php echo $link; ?>" title="<?php echo JText::_('COM_JMAP_EDIT_SOURCE' ); ?>">
						<?php echo $row->name; ?></a>
					<?php
				}
				?>
			</td>
			<td align="center">
				<?php echo $row->type; ?>
			</td>
			<td align="center">
				<?php echo $row->description; ?>
			</td>
			
			<td class="order">
				<?php 
				$ordering = $this->orders['order'] == 's.ordering'; 
				$disabled = $ordering ?  '' : 'disabled="disabled"'; ?>
				<span><?php echo $this->pagination->orderUpIcon( $i, true, 'sources.moveorder_up', 'COM_JMAP_MOVE_UP', $ordering); ?></span>
				<span><?php echo $this->pagination->orderDownIcon( $i, $n, true, 'sources.moveorder_down', 'COM_JMAP_MOVE_DOWN', $ordering); ?></span>
				<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>"  <?php echo $disabled; ?>  class="text_area" style="text-align: center" />
			</td>
					
			<td align="center">
				<?php echo $published;?>
			</td>
			<td align="center">
				<?php echo $row->id; ?>
			</td>
		</tr>
		<?php
	}
	?>
	<tfoot>
		<td colspan="13">
			<?php echo $this->pagination->getListFooter(); ?>
		</td>
	</tfoot>
	</table>

	<input type="hidden" name="section" value="view" />
	<input type="hidden" name="option" value="<?php echo $this->option;?>" />
	<input type="hidden" name="task" value="sources.display" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo @$this->orders['order'];?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo @$this->orders['order_Dir'];?>" />
</form>