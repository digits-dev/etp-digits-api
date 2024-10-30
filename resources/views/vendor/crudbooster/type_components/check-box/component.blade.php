<div class='form-group {{$header_group_class}} {{ ($errors->first($name))?"has-error":"" }}' id='form-group-{{$name}}' style="{{@$form['style']}}">
    <label class='control-label col-sm-2'>{{$form['label']}}
        @if($required)
            <span class='text-danger' title='{!! trans('crudbooster.this_field_is_required') !!}'>*</span>
        @endif
    </label>
    
        @if($form['parent_select'])
        <?php
            $parent_select  = (count(explode(",",$form['parent_select'])) > 1)?explode(",",$form['parent_select']):$form['parent_select'];
            $parent         = is_array($parent_select)?$parent_select[0]:$parent_select;
            $add_field      = is_array($parent_select)?$parent_select[1]:'';
        ?>
        <div class="{{$col_width?:'col-sm-10'}}" id="cblist">
        <div data-val='DEFAULT-STORE' class='checkbox {{ $disabled }}' id="cblist1">
            <label for="cb0">
               <input type='checkbox' {{ $disabled }} {{ $checked }} name='{{$name}}[]' value='' id="cb1">
               TST-DEFAULT STORE                               
            </label>
        </div>
        </div>
        @push('bottom')
        <script type="text/javascript">
            $(function() {          
                $('#{{$parent}}, input:radio[name={{$parent}}]').change(function() {
                    
                    var $current = $("#{{$form['name']}}");
                    var parent_id = $(this).val();
                    var fk_name = "{{$parent}}";
                    var fk_value = $(this).val();
                    var datatable = "{{$form['datatable']}}".split(',');
                    @if(!empty($add_field))
                    var add_field = ($("#{{$add_field}}").val())?$("#{{$add_field}}").val():"";
                    @endif
                    var datatableWhere = "{{$form['datatable_where']}}";
                    @if(!empty($add_field))
                    if(datatableWhere) {
                        if(add_field) {
                            datatableWhere = datatableWhere + " and {{$add_field}} = " + add_field; 
                        }
                    }else{
                        if(add_field) {
                            datatableWhere = "{{$add_field}} = " + add_field; 
                        }
                    }
                    @endif
                    var table = datatable[0].trim('');
                    var label = datatable[1].trim('');
                    var value = "{{$value}}"; 
                    var values = value.split(",");

                    if(fk_value!='') {
                        
                        //$current.html("");
                        $.get("{{CRUDBooster::mainpath('data-table')}}?table="+table+"&label="+label+"&fk_name="+fk_name+"&fk_value="+fk_value+"&datatable_where="+encodeURI(datatableWhere),function(response) {
                            if(response) {
                                //$current.html("");
                                var container = $('#cblist');
                                container.empty();
                                $.each(response,function(i,obj) {

                                    //var checked = (value && value == obj.select_value)?"checked":"";
                                    var string = String(obj.select_value);
                                    var checked_item = (values.includes(string))?"checked":"";
                                    var divhtml = $('<div/>', { 'data-val': obj.select_label, 'class' : 'checkbox' }).appendTo(container);
                                    var labelhtml = $('<label/>', { 'for': 'cb'+obj.select_value, text: obj.select_label }).appendTo(divhtml);
                                    if(checked_item){
                                        $('<input/>', { type: 'checkbox', id: 'cb'+obj.select_value, value: obj.select_value, name: '{{$form["name"]}}'+'[]', checked: checked_item }).prependTo(labelhtml);
                                    }
                                    else{
                                        $('<input/>', { type: 'checkbox', id: 'cb'+obj.select_value, value: obj.select_value, name: '{{$form["name"]}}'+'[]' }).prependTo(labelhtml);
                                    }
                                    
                                    
                                })                          
                            }                       
                        });
                    }else{
                        //$current.html("");
                    }   
                    //console.log(JSON.stringify(values));
                })

                $('#{{$parent}}').trigger('change');
                $("input[name='{{$parent}}']:checked").trigger("change");
                $("#{{$form['name']}}").trigger('change');
            })
        </script>
        @endpush

        @endif

        @if($form['dataenum']!='' && !$form['parent_select'])
            <?php
            @$value = explode(";", $value);
            @array_walk($value, 'trim');
            $dataenum = $form['dataenum'];
            $dataenum = (is_array($dataenum)) ? $dataenum : explode(";", $dataenum);
            $counter=0;
            ?>
            @foreach($dataenum as $k=>$d)
                <?php
                $counter++;
                if (strpos($d, '|')) {
                    $val = substr($d, 0, strpos($d, '|'));
                    $label = substr($d, strpos($d, '|') + 1);
                } else {
                    $val = $label = $d;
                }
                $checked = ($value && in_array($val, $value)) ? "checked" : "";
                ?>

                <?php
                if($counter%20 == 1)
                {
                ?>
                <div class="{{$col_width?:'col-sm-4'}}">
                <?php
                }
                ?>

                <div class="checkbox {{$disabled}}">
                    <label>
                        <input type="checkbox" {{$disabled}} {{$checked}} name="{{$name}}[]" value="{{$val}}"> {{$label}}
                    </label>
                </div>

                <?php
                if($counter%20 == 0)
                {
                ?>
                    <div class="text-danger">{!! $errors->first($name)?"<i class='fa fa-info-circle'></i> ".$errors->first($name):"" !!}</div>
                    <p class='help-block'>{{ @$form['help'] }}</p>
                </div>
                <?php
                }
                ?>
            @endforeach
        @endif

        <?php
        if (@$form['datatable'] && !$form['parent_select']):
            $datatable_array = explode(",", $form['datatable']);
            $datatable_tab = $datatable_array[0];
            $datatable_field = $datatable_array[1];

            $tables = explode('.', $datatable_tab);
            $selects_data = DB::table($tables[0])->select($tables[0].".id");

            if (\Schema::hasColumn($tables[0], 'deleted_at')) {
                $selects_data->where('deleted_at', NULL);
            }

            if (@$form['datatable_where']) {
                $selects_data->whereraw($form['datatable_where']);
            }

            if (count($tables)) {
                for ($i = 1; $i <= count($tables) - 1; $i++) {
                    $tab = $tables[$i];
                    $selects_data->leftjoin($tab, $tab.'.id', '=', 'id_'.$tab);
                }
            }

            $selects_data->addselect($datatable_field);

            $selects_data = $selects_data->orderby($datatable_field, "asc")->get();

            if ($form['relationship_table']) {
                $foreignKey = CRUDBooster::getForeignKey($table, $form['relationship_table']);
                $foreignKey2 = CRUDBooster::getForeignKey($datatable_tab, $form['relationship_table']);

                $value = DB::table($form['relationship_table'])->where($form['relationship_table'].'.'.$foreignKey, $id);
                $value = $value->pluck($foreignKey2)->toArray();
                $counter=0;

                foreach ($selects_data as $d) {
                    $counter++;
                    $checked = (is_array($value) && in_array($d->id, $value)) ? "checked" : "";
                    
                    if($counter%20 == 1)
                    {
        ?>
                    <div class="{{$col_width?:'col-sm-4'}}">
        <?php
                    }
           
                    echo "
					<div data-val='$val' class='checkbox $disabled'>
    					<label>
    					   <input type='checkbox' $disabled $checked name='".$name."[]' value='".$d->id."'> ".$d->{$datatable_field}."								    
    					</label>
					</div>";

                    if($counter%20 == 0)
                    {
        ?>
                        <div class="text-danger">{!! $errors->first($name)?"<i class='fa fa-info-circle'></i> ".$errors->first($name):"" !!}</div>
                        <p class='help-block'>{{ @$form['help'] }}</p>
                        </div>
        <?php
                    }
                }
            } else {
                @$value = explode(';', $value);
                $counter=0;

                foreach ($selects_data as $d) {
                    $counter++;
                    $val = $d->{$datatable_field};
                    $checked = (is_array($value) && in_array($val, $value)) ? "checked" : "";
                    if ($val == '' || ! $d->id) continue;

                    if($counter%20 == 1)
                    {
        ?>
                        <div class="{{$col_width?:'col-sm-10'}}">
        <?php
                    }

                    echo "
					<div data-val='$val' class='checkbox $disabled'>
						<label>
							<input type='checkbox' $disabled $checked name='".$name."[]' value='".$d->id."'> ".$val." 								    
						</label>
					</div>";

                    if($counter%20 == 0)
                    {
        ?>
                        <div class="text-danger">{!! $errors->first($name)?"<i class='fa fa-info-circle'></i> ".$errors->first($name):"" !!}</div>
                        <p class='help-block'>{{ @$form['help'] }}</p>
                        </div>
        <?php
                    }
                }
            }

        endif;
        if ($form['dataquery'] && !$form['parent_select']) {

            $query = DB::select(DB::raw($form['dataquery']));
            @$value = explode(';', $value);
            $counter=0;

            if ($query) {
                foreach ($query as $q) {
                    $val = $q->value;
                    $checked = (is_array($value) && in_array($val, $value)) ? "checked" : "";
                    //if($val == '' || !$d->id) continue;
                    
                    if($counter%20 == 1)
                    {
        ?>
                    <div class="{{$col_width?:'col-sm-10'}}">
        <?php
                    }

                    echo "
					<div data-val='$val' class='checkbox $disabled'>
                        <label>
                            <input type='checkbox' $disabled $checked name='".$name."[]' value='$q->value'> ".$q->label." 								    
                        </label>
					</div>";

                    if($counter%20 == 0)
                    {
        ?>
                        <div class="text-danger">{!! $errors->first($name)?"<i class='fa fa-info-circle'></i> ".$errors->first($name):"" !!}</div>
                        <p class='help-block'>{{ @$form['help'] }}</p>
                        </div>
        <?php
                    }
                }
            }
        }
        ?>
        <!-- <div class="text-danger">{!! $errors->first($name)?"<i class='fa fa-info-circle'></i> ".$errors->first($name):"" !!}</div>
        <p class='help-block'>{{ @$form['help'] }}</p>
    </div> -->
</div>