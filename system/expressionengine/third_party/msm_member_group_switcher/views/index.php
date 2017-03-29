


<?=form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=msm_member_group_switcher'.AMP.'method=index', array('id'=>'msm_member_group_switcher_search_form'))?>
	<div id="filterMenu">
		<fieldset>
			<legend><?=lang('total_members')?> <?=$total_count?></legend>

			<p>
				<?=form_label(lang('keywords').NBS, 'search', array('class' => 'field js_hide'))?>
				<?=form_input(array('id'=>'keyword', 'name'=>'keyword', 'class'=>'field', 'placeholder' => lang('keywords'), 'value'=>$selected['keyword']))?> 
                
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

				<?=form_label(lang('filter_by'), 'filter_by')?>&nbsp;
				<?=form_dropdown('filter_by', $filter_by, $selected['filter_by'], 'id="filter_by"')?> 
				
				
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;


				<?=form_submit('submit', lang('search'))?>
                
			</p>

		</fieldset>
	</div>
<?=form_close()?>



<div style="padding: 10px;">

<?php if ($total_count == 0):?>
	<div class="tableFooter">
		<p class="notice"><?=lang('no_results')?></p>
	</div>
<?php else:?>

	<?php
		$this->table->set_template($cp_table_template);
		$this->table->set_heading($table_headings);

		echo $this->table->generate($members);
	?>



<span class="pagination"><?=$pagination?></span>


<?php endif; /* if $total_count > 0*/?>

</div>


