<?php 
	define("CACHE_KEY_ACCOUNTS","select id,uname,passwd from aw_accounts");
	function aw_accounts(&$cas=null){
		global $cache;
		$sql = aw_accounts_sql();
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			
			$list = $db->vals($sql,true);
			$val = array();
			foreach($list as $l){
				$val[0][$l['uname']] = array($l['id'],$l['passwd']);
			}
			
			$cache->set($key,$val);
		}
		
		return $val;
	}
	function aw_accounts_sql(){
		return CACHE_KEY_ACCOUNTS;
	}
	
	define("CACHE_KEY_ADDITION_COUNTRY_MAP","select id,_type,_value,addition_type,addition_value,addition_num,_percent,start_time,death_time,_country from aw_addition_country_map");
	function aw_addition_country_map(&$cas=null){
		global $cache;
		$sql = CACHE_KEY_ADDITION_COUNTRY_MAP;
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			
			$additions = $db->vals($sql,true);
			foreach($additions as $a){
				$val[$a['_country']][0][$a['_type']][$a['_value']][$a['addition_type']] = array($a['id'],$a['addition_value'],$a['addition_num'],$a['_percent'],$a['start_time'],$a['death_time']);
				$val[$a['_country']][1][$a['addition_type']][] = array($a['_type'],$a['_value']);
			}
			$cache->set($key, $val);
		}
		return $val;
	}
	define("CACHE_KEY_ADDITION_MAP","select id,_type,_value,addition_type,addition_value,addition_num,_percent,start_time,death_time from aw_addition_map where _cid=");
	function aw_addition_map($cid,&$cas=null){
		global $cache;
		$sql = aw_addition_map_sql($cid);
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			
			$additions = $db->vals($sql,true);
			foreach($additions as $a){
				$val[0][$a['_type']][$a['_value']][$a['addition_type']] = array($a['id'],$a['addition_value'],$a['addition_num'],$a['_percent'],$a['start_time'],$a['death_time']);
				$val[1][$a['addition_type']][] = array($a['_type'],$a['_value']);
			}
			$cache->set($key, $val);
		}
		return $val;
	}
	function aw_addition_map_sql($cid){
		return CACHE_KEY_ADDITION_MAP."'$cid'";
	}
	define("CACHE_KEY_BLACK_MARKET","select id,cid,item_id,_num,_price from aw_black_market_temp");
	/**
	 * 黑市、黑市商人缓存，cid为0是商城黑市出售信息
	 * $val[0]['cid']['item_id'] = array("id","_num","_price");
	 * 黑市商人位置信息
	 * $val[1][0] = array("_country","_line","_index","_last_time");
	 * 黑市商人购买信息
	 * $val[2][0][] = array("_time","_nickname","item_id","item_num")
	 */
	function aw_black_market(&$cas=null){
		global $cache;
		$sql = aw_black_market_sql();
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			$black = $db->vals($sql,true);
			foreach($black as $b){
				$val[0][$b['cid']][$b['item_id']] = array($b['id'],$b['_num'],$b['_price']);
			}
			$sql_black_person = "select _country,_line,_index,_last_time from aw_black_market_option";
			$person = $db->val($sql_black_person);
			$val[1][0] = array($person['_country'],$person['_line'],$person['_index'],$person['_last_time']);
			$sql_record = "select _time,_nickname,item_id,item_num from aw_black_market_record order by _time desc";
			$record = $db->vals($sql_record,true);
			foreach($record as $r){
				$val[2][0][] = array($r['_time'],$r['_nickname'],$r['item_id'],$r['item_num']);
			}
			$cache->set($key, $val);
		}
		return $val;
	}
	function aw_black_market_sql(){
		return CACHE_KEY_BLACK_MARKET;
	}
	define("CACHE_KEY_BUILDING","select id,_type,_base_hp,_name from aw_building order by id");
	/**
	 * 建筑基本信息<br>
	 * $val[0]['id'][0] = array("_type","_base_hp","_name");<br>
	 * 建筑建造基本消耗<br>
	 * $val[0]['id'][1] = array("res_k","time_k","cost_rice","cost_wood","cost_stone","cost_oil","cost_time","cost_rmb","cost_money");<br>
	 * 0 = res_k	<br>
	 * 1 = time_k	<br>
	 * 2 = cost_rice	<br>
	 * 3 = cost_wood	<br>
	 * 4 = cost_stone	<br>
	 * 5 = cost_oil	<br>
	 * 6 = cost_time	<br>
	 * 7 = cost_rmb	<br>
	 * 8 = cost_money	<br>
	 * 建筑建造前置条件<br>
	 * $val[0]['id'][2][] = array("_type","_value","_num");	<br>
	 * 建筑时代最大数<br>
	 * 删除（$val[0]['id'][3]['times'] = $num	）<br>
	 * 建筑可学科技
	 * $val[0]['id'][4][] = $tech_id;
	 * 建筑可训练单位
	 * $val[0]['id'][5][] = $units_id;
	 * 建筑分类<br>
	 * $val[1]['_type'][] = $id;	<br>
	 */
	function aw_building(){
		global $cache;
		$sql = CACHE_KEY_BUILDING;
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			$buildings = $db->vals($sql,true);
			$val = array();
			foreach($buildings as $v){
				$val[0][$v['id']][0] = array($v['_type'],$v['_base_hp'],$v['_name']);
				$val[1][$v['_type']][] = $v['id'];
				//建筑基本消耗
				$sql_attribute = "select res_k,time_k,cost_rice,cost_wood,cost_stone,cost_oil,cost_time,cost_rmb,cost_money from aw_building_attribute where build_id='{$v['id']}'";
				$attr = $db->val($sql_attribute);
				$val[0][$v['id']][1] = array($attr['res_k'],$attr['time_k'],$attr['cost_rice'],$attr['cost_wood'],$attr['cost_stone'],$attr['cost_oil'],$attr['cost_time'],$attr['cost_rmb'],$attr['cost_money']);
				//建筑前置条件
				$sql_need = "select _type,_value,_num from aw_building_need where build_id='{$v['id']}'";
				$needs = $db->vals($sql_need,true);
				if(!empty($needs)){
					foreach($needs as $n){
						$val[0][$v['id']][2][] = array($n['_type'],$n['_value'],$n['_num']);
					}
				}
				//建筑时代最大数
// 				$sql_max = "select _times,_max_num from aw_building_max_times where build_id='{$v['id']}' order by _times";
// 				$maxs = $db->vals($sql_max,true);
// 				foreach($maxs as $m){
// 					$val[0][$v['id']][3][$m['_times']] = $m['_max_num'];
// 				}
				//建筑可学科技
				$sql_tech = "select tech_id from aw_building_tech where build_id='{$v['id']}'";
				$techs = $db->vals($sql_tech,true);
				if(!empty($techs)){
					foreach($techs as $t){
						$val[0][$v['id']][4][] = $t['tech_id'];
					}
				}
				//建筑可训练单位
				$sql_units = "select units_id from aw_building_units where build_id='{$v['id']}'";
				$units = $db->vals($sql_units,true);
				if(!empty($units)){
					foreach($units as $u){
						$val[0][$v['id']][5][] = $u['units_id'];
					}
				}
			}
			$cache->set($key,$val);
		}
		return $val;
	}
	
	define("CACHE_KEY_BUILDING_MAP","select id,bld_id,bld_lv,_posx,_posy,_workers,_pro_id,_max_worker,_status,_hp,_hpmax,_high_lv,_job from aw_building_map where cid=");
	/**
	 * 按角色ID返回角色建筑<br>
	 * 建立索引($val[1]['id'] = $bld_id),如果查询的id为建筑主键ID，先通过索引查询建筑类型ID，然后通过aw_building查询建筑的_type再从本方法返回的数据中查询建筑数据
	 * $val[0]['bld_id']['id'] = array(bld_lv,_posx,_posy,_workers,_pro_id,_max_worker,_status,_hp,_hpmax,_high_lv,_job);<br>
	 * $val[1] = 索引
	 * 0=>bld_lv  <br>
	 * 1=>_posx  <br>
	 * 2=>_posy  <br>
	 * 3=>_workers  <br>
	 * 4=>_pro_id  <br>
	 * 5=>_max_worker  <br>
	 * 6=>_status  <br>
	 * 7=>_hp  <br>
	 * 8=>_hpmax  <br>
	 * 9=>_high_lv  <br>
	 * 10=>_job  <br>
	 * 建筑CD列表
	 * $val[2]['building_id'] = array("start_time","end_time")
	 * 当前所有建筑工作人口总和
	 * $val[3] = $workers
	 * @param int $cid
	 * @param int $cas 引用参数，传递出数据的版本号
	 */
	function aw_building_map($cid,&$cas=null){
		global $cache;
		$sql = aw_building_map_sql($cid);
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			$buildings = $db->vals($sql,true);
			$val = array();
			$aw_building = aw_building();
			foreach($buildings as $v){
// 				$type = $aw_building[0][$v['bld_id']][0][0];
// 				$val[0][$type][$v['bld_id']][$v['id']] = array($v['bld_lv'],$v['_posx'],$v['_posy'],$v['_workers'],$v['_pro_id'],$v['_max_worker'],$v['_status'],$v['_hp'],$v['_hpmax'],$v['_high_lv'],$v['_job']);
				$val[0][$v['bld_id']][$v['id']] = array($v['bld_lv'],$v['_posx'],$v['_posy'],$v['_workers'],$v['_pro_id'],$v['_max_worker'],$v['_status'],$v['_hp'],$v['_hpmax'],$v['_high_lv'],$v['_job']);
				$val[1][$v['id']] = $v['bld_id'];
				$val[3] += $v['_workers'];
			}
			$time_now = time();
			$db->query("delete from aw_building_cd_map where end_time<='{$time_now}' and cid='{$cid}'");
			$sql_cd = "select building_id,start_time,end_time from aw_building_cd_map where cid='{$cid}' and end_time>'{$time_now}'";
			$cd = $db->vals($sql_cd,true);
			foreach($cd as $c){
				$val[2][$c['building_id']] = array($c['start_time'],$c['end_time']);
			}
			$cache->set($key, $val);
		}
		return $val;
	}
	/**
	 * 根据角色id返回sql语句
	 * @param unknown_type $cid
	 * @return string
	 */
	function aw_building_map_sql($cid){
		return CACHE_KEY_BUILDING_MAP."'{$cid}' order by bld_id";
	}
	define("CACHE_KEY_CDMAX_MAP","select id,cid,cd_max,cd_type,start_time,end_time from aw_cdmax_map");
	function aw_cdmax_map(&$cas=null){
		global $cache;
		$sql = aw_cdmax_map_sql();
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			$cds = $db->vals($sql,true);
			foreach($cds as $c){
				if($c['cd_type']==3){
					$val[1][$c['cid']][$c['cd_type']][$c['cd_max']] = array($c['start_time'],$c['end_time'],$c['id']);
				}else{
					$val[0][$c['cid']][$c['cd_type']] = array($c['cd_max'],$c['start_time'],$c['end_time'],$c['id']);
				}
			}
			$cache->set($key, $val);
		}
		return $val;
	}
	function aw_cdmax_map_sql(){
		return CACHE_KEY_CDMAX_MAP;
	}
	define("CACHE_KEY_COMPANY_FORMULATION","select id,_job,item_id,_proficiency,_price from aw_company_make");
	function aw_company_formulation(&$cas=null){
		global $cache;
		$sql = aw_company_formulation_sql();
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			
			$formulation = $db->vals($sql,true);
			$val = array();
			foreach($formulation as $f){
				$val[0][$f['id']][0] = array($f['_job'],$f['item_id'],$f['_proficiency'],$f['_price']);
				$sql_formulation = "select _type,_value,_num from aw_company_formulation where item_id='{$f['item_id']}'";
				$need = $db->vals($sql_formulation,true);
				foreach($need as $n){
					$val[0][$f['id']][1][] = array($n['_type'],$n['_value'],$n['_num']);
				}
			}
			$cache->set($key,$val);
		}
		
		return $val;
	}
	function aw_company_formulation_sql(){
		return CACHE_KEY_COMPANY_FORMULATION;
	}
	define("CACHE_KEY_COMPANY_MADE_MAP", "select id,cid,item_id,item_num,_finish_time,_start_time,_death_time,_type from aw_company_made_temp order by id");
	/**
	 * 公司培育
	 * $val[0]['cid'][0]['id'] = array("item_id","item_num","_finish_time","_start_time","_death_time","_type");
	 * 索引
	 * $val[0]['cid'][1]['_type'] = $id;
	 * 正在培育的土地索引
	 * $val[0]['cid'][2][] = $id;
	 */
	function aw_company_made_map(&$cas=null){
		global $cache;
		$sql = aw_company_made_map_sql();
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			
			$list = $db->vals($sql,true);
			$val = array();
			foreach($list as $l){
				$val[0][$l['cid']][0][$l['id']] = array($l['item_id'],$l['item_num'],$l['_finish_time'],$l['_start_time'],$l['_death_time'],$l['_type']);
				$val[0][$l['cid']][1][$l['_type']][] = $l['id'];
				if($l['item_id']>0){
					$val[0][$l['cid']][2][] = $l['id'];
				}
			}
			
			$cache->set($key, $val);
		}
		return $val;
	}
	
	function aw_company_made_map_sql(){
		return CACHE_KEY_COMPANY_MADE_MAP;
	}
	define("CACHE_KEY_COMPANY_MADE","select id,cid,make_id,item_id,_job from aw_company_map");
	/**
	 * 
	 * @return multitype:
	 */
	function aw_company_map(&$cas=null){
		global $cache;
		$sql = aw_company_map_sql();
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			
			$map = $db->vals($sql,true);
			$val = array();
			foreach($map as $m){
				$val[0][$m['cid']][0][$m['item_id']] = array($m['id'],$m['make_id'],$m['_job']);
				$val[0][$m['cid']][1][$m['make_id']] = $m['item_id'];
				$val[0][$m['cid']][2][$m['_job']][] = $m['item_id'];
			}
			
			$cache->set($key,$val);
		}
		
		return $val;
	}
	function aw_company_map_sql(){
		return CACHE_KEY_COMPANY_MADE;
	}
	
	define("CACHE_KEY_COMPETE_ATK_TEAM","select id,_cid,_nickname,_time,atk_type,_name,_max,_now_fight from aw_compete_atk_team where country_id=");
	/**
	 * 争夺战缓存
	 * $val[0]['atk_type']['id'][0] = array("_cid","_nickname","_time","_name","_max","_now_fight");
	 * 0 = _cid <br>
	 * 1 = _nickname <br>
	 * 2 = _time <br>
	 * 3 = _name <br>
	 * 4 = _max <br>
	 * 5 = _now_fight <br>
	 * 队伍成员
	 * $val[0]['atk_type']['id'][1][] = array("_cid","_nickname");
	 * 索引
	 * $val[1]['id'] = $atk_type
	 * 角色建立的队伍
	 * $val[2]['cid'][0] = $id;
	 * 角色的所有队伍
	 * $val[2]['cid'][1][] = $id;
	 */
	function aw_compete_atk_team($country,&$cas=null){
		global $cache;
		$sql = aw_compete_atk_team_sql($country);
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			$teams = $db->vals($sql,true);
			foreach($teams as $t){
				$val[0][$t['atk_type']][$t['id']][0] = array($t['_cid'],$t['_nickname'],$t['_time'],$t['_name'],$t['_max'],$t['_now_fight']);
				$sql_1 = "select id,_cid,_nickname from aw_compete_assist_atk_person where team_id='{$t['id']}'";
				$person = $db->vals($sql_1,true);
				foreach($person as $p){
					$val[0][$t['atk_type']][$t['id']][1][] = array($p['_cid'],$p['_nickname'],$p['id']);
					$val[2][$p['_cid']][1][] = $t['id'];
				}
				$val[1][$t['id']] = $t['atk_type'];
				$val[2][$t['_cid']][0] = $t['id'];
			}
			$cache->set($key, $val);
		}
		return $val;
	}
	function aw_compete_atk_team_sql($country){
		return CACHE_KEY_COMPETE_ATK_TEAM."'{$country}' order by _time desc,id";
	}
	define("CACHE_KEY_COMPETE_DEF_ARMY","select id,units_id,units_num,country_id,compete_type,_order from aw_compete_def_army order by country_id,compete_type,_order");
	/**
	 * 返回国家争夺战军力配置
	 * _order是为了文件排序
	 * 单位列表
	 * $val['country_id']['compete_type'][0]['_order'] = array("id","units_id","units_num")
	 * 飞弹列表
	 * $val['country_id']['compete_type'][1]['_order'] = array("id","units_id","units_num")
	 * 0 = id	<br>
	 * 1 = units_id	<br>
	 * 2 = units_num	<br>
	 * @param unknown_type $cas
	 * @return multitype:unknown
	 */
	function aw_compete_def_army(&$cas=null){
		global $cache;
		$sql = aw_compete_def_army_sql();
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			$armys = $db->vals($sql,true);
			$aw_units = aw_units();
			foreach($armys as $v){
				$type = $aw_units[0][$v['units_id']][0][0];
				if($type!=2){
					$val[$v['country_id']][$v['compete_type']][0][$v['_order']] = array($v['id'],$v['units_id'],$v['units_num']);
				}else{
					$val[$v['country_id']][$v['compete_type']][1][$v['_order']] = array($v['id'],$v['units_id'],$v['units_num']);
				}
			}
			$cache->set($key, $val);
		}
		return $val;
	}
	function aw_compete_def_army_sql(){
		return CACHE_KEY_COMPETE_DEF_ARMY;
	}
	define("CACHE_KEY_CONFIG","select id,_type from aw_config");
	/**
	 * $val['_type'][] = $id;
	 */
	function aw_config(){
		global $cache;
		$sql = CACHE_KEY_CONFIG;
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			$configs = $db->vals($sql,true);
			$val = array();
			foreach($configs as $v){
				$val[$v['_type']][] = $v['id'];
			}
			$cache->set($key,$val);
		}
		return $val;
	}
	define("CACHE_KEY_COUNTRY","select id,_name,_continents,_culture,_king,_pro,_gen,_election_time,_election_switch,_city_count,_money,_stone,_oil,_re,_notice,_money_k,_war_k,_country_hp,_country_safe from aw_country");
	/**
	 * 同一个国家的数据也是共享的，所以直接将国家表映射成一个缓存表
	 * $val['id'][0] = array("_continents","_culture","_king","_pro","_gen","_election_time","_election_switch","_city_count","_money","_stone","_oil","_re","_notice","_money_k","_war_k","_country_hp","_country_safe");
	 * 0=_continents
	 * 1=_culture
	 * 2=_king
	 * 3=_pro
	 * 4=_gen
	 * 5=_election_time
	 * 6=_election_switch
	 * 7=_city_count
	 * 8=_money
	 * 9=_stone
	 * 10=_oil
	 * 11=_re
	 * 12=_notice
	 * 13=_money_k
	 * 14=_war_k
	 * 15=_country_hp
	 * 16=_country_safe
	 * 17=_name
	 * 国家附属国
	 * $val['id'][1] = array("id","affiliated_country","_time");
	 * 国家最高时代
	 * $val['id'][2] = $max_times;
	 * @param unknown_type $cas
	 * @return multitype:unknown
	 */
	function aw_country(&$cas=null){
		global $cache;
		$sql = aw_country_sql();
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			$country = $db->vals($sql,true);
			$time_now = time();
			foreach($country as $v){
				$val[$v['id']][0] = array($v['_continents'],$v['_culture'],$v['_king'],$v['_pro'],$v['_gen'],$v['_election_time'],$v['_election_switch'],$v['_city_count'],$v['_money'],$v['_stone'],$v['_oil'],$v['_re'],$v['_notice'],$v['_money_k'],$v['_war_k'],$v['_country_hp'],$v['_country_safe'],$v['_name']);
			}
			$sql_1 = "select id,affiliated_country,_time,_country from aw_country_affiliated where  _time>'{$time_now}' order by _time";
			$affiliated = $db->vals($sql_1,true);
			if(!empty($affiliated)){
				foreach($affiliated as $a){
					$val[$a['_country']][1] = array($a['id'],$a['affiliated_country'],$a['_time']);
					$val[$a['affiliated_country']][2] = $a['_country'];
				}
			}
			$sql_2 = "select max(_times) as max,_country from aw_user where _nickname is not null group by _country";
			$max_times = $db->vals($sql_2,true);
			foreach ($max_times as $m){
				$val[$m['_country']][2] = $m['max'];
			}
			$cache->set($key, $val);
		}
		return $val;
	}
	function aw_country_sql(){
		return CACHE_KEY_COUNTRY;
	}
	define("CACHE_KEY_COUNTRY_LOG","select id,_type,_value,_num,cid,_content,_time,_nickname from aw_country_log");
	/**
	 * 国家国志缓存
	 * $val[0]['_type']['id'] = array("_value","_num","cid","_content","_time","_nickname")
	 * 0 = _value	<br>
	 * 1 = _num	<br>
	 * 2 = cid	<br>
	 * 3 = _content	<br>
	 * 4 = _time	<br>
	 * 5 = _nickname	<br>
	 * 索引
	 * $val[1]['_type']['_value']['_num'] = $id;
	 * 当前国王届数
	 * $val[2] = $max_num;
	 * @param unknown_type $cas
	 * @return multitype:unknown
	 */
	function aw_country_log($country,&$cas=null){
		global $cache;
		$sql = aw_country_log_sql($country);
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			$logs = $db->vals($sql,true);
			foreach($logs as $l){
				$val[0][$l['_type']][$l['id']] = array($l['_value'],$l['_num'],$l['cid'],$l['_content'],$l['_time'],$l['_nickname']);
				$val[1][$l['_type']][$l['_value']][$l['_num']] = $l['id'];
				
				if($l['_type']==1 && $l['_num']>$val[2]){
					$val[2] = $l['_num'];
				}
			}
			$cache->set($key, $val);
		}
		return $val;
	}
	function aw_country_log_sql($country){
		return CACHE_KEY_COUNTRY_LOG." where _country='{$country}'";
	}
	define("CACHE_KEY_COUNTRY_WAR","select id,atk_country,def_country,_time from aw_country_war");
	/**
	 * 返回国战相关信息
	 * $val[0]['id'] = array("atk_country","def_country","_time")
	 * $val[1]['country'] = $id;
	 */
	function aw_country_war(&$cas=null){
		global $cache;
		$sql = aw_country_war_sql();
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		$war_start = get_country_war_start_time();
		$war_end = get_country_war_end_time($war_start);
		$apply_start = get_apply_country_war_start_time($war_start);
		$apply_end = get_apply_country_war_end_time($war_start);
		
		//如果缓存中不存在或者国战开始的时间与缓存中的时间不一致，重新读取数据
		if($cache->getResultCode() == Memcached::RES_NOTFOUND || $val[2][0] != $war_start){
			$val = array();
			$db = new d5db();
			$db->connect();
			$sql .= " where  _time>'{$apply_start}' and _time<'{$apply_end}'";
			$wars = $db->vals($sql,true);
			foreach($wars as $w){
				$val[0][$w['id']] = array($w['atk_country'],$w['def_country'],$w['_time']);
				$val[1][$w['atk_country']] = $w['id'];
				$val[1][$w['def_country']] = $w['id'];
			}
			$val[2][0] = $war_start;
			$val[2][1] = $war_end;
			$val[2][2] = $apply_start;
			$val[2][3] = $apply_end;
			$cache->set($key, $val);
		}
		return $val;
	}
	function aw_country_war_sql(){
		return CACHE_KEY_COUNTRY_WAR;
	}
	define("CACHE_KEY_DEFENSE_OPTION","select id,cid,units_id,_num,_posx,_posy,_type,_atk,_atk_air,_def,_def_air,_hp,_view,_speed from aw_defense_army");
// 	define("CACHE_KEY_DEFENSE_OPTION","select id,cid,units_id,_num,_posx,_posy from aw_defense_army");
	/**
	 * 防御配兵设置
	 * $val[0]['cid'][0][] = array("id","units_id","_num","_posx","_posy");
	 * 是否使用飞弹防御设置,是否自动补兵
	 * $val[0]['cid'][1] = array("id","_type","isFillUpArmy");
	 * @param unknown_type $cas
	 */
	function aw_defense_option(&$cas=null){
		global $cache;
		$sql =  aw_defense_option_sql();
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			
			$armys = $db->vals($sql,true);
			foreach($armys as $a){
				$val[0][$a['cid']][0][] = array($a['id'],$a['units_id'],$a['_num'],$a['_posx'],$a['_posy'],$a['_type'],$a['_atk'],$a['_atk_air'],$a['_def'],$a['_def_air'],$a['_hp'],$a['_view'],$a['_speed']);
// 				$val[0][$a['cid']][0][] = array($a['id'],$a['units_id'],$a['_num'],$a['_posx'],$a['_posy']);
			}
			$sql_option = "select cid,id,_type,isFillUpArmy from aw_defense_option";
			$option = $db->val($sql_option);
			$val[0][$option['cid']][1] = array($option['id'],$option['_type'],$option['isFillUpArmy']);
			$cache->set($key, $val);
		}
		return $val;
	}
	function aw_defense_option_sql(){
		return CACHE_KEY_DEFENSE_OPTION;
	}
	define("CACHE_KEY_DUNGEON","select id,_type,_lv,_name from aw_dungeon order by id");
	/**
	 * 副本配置缓存
	 * $val[0]['_type']['dungeon_id'][0] = array("_lv","_name");
	 * 节点配置
	 * $val[0]['_type']['dungeon_id'][1]['order'][0] = array("node_id","_safe_time","_lv","_name");
	 * 节点专家
	 * $val[0]['_type']['dungeon_id'][1]['order'][1] = array("_head","_potential","_int","_com","_pol","_lv");
	 * 节点配兵
	 * $val[0]['_type']['dungeon_id'][1]['order'][2][] = array("units_id","units_num");
	 * 节点奖励
	 * $val[0]['_type']['dungeon_id'][1]['order'][3][] = array("_type","_value","_num","_percent");
	 * 副本科技
	 * $val[0]['_type']['dungeon_id'][2]['tech_id'] = array("tech_lv");
	 * 索引配置
	 * $val[1]['node_id'] = array("_type","dungeon_id","_order");
	 * 用户副本记录
	 * $val[2]['cid'][0]['type'][] = array("id","dungeon_id","node_id");
	 */
	function aw_dungeon(&$cas=null){
		global $cache;
		$sql = aw_dungeon_sql();
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			$dungeons = $db->vals($sql,true);
			foreach($dungeons as $d){
				$val[0][$d['_type']][$d['id']][0] = array($d['_lv'],$d['_name']);
				$sql_node = "select id,_order,_safe_time,_lv,_name from aw_dungeon_node where dungeon_id='{$d['id']}' order by _order";
				$nodes = $db->vals($sql_node,true);
				foreach($nodes as $n){
					$val[0][$d['_type']][$d['id']][1][$n['_order']][0] = array($n['id'],$n['_safe_time'],$n['_lv'],$n['_name']);
					$sql_node_pro = "select _head,_potential,_int,_com,_pol,_lv from aw_dungeon_node_pro where node_id='{$n['id']}'";
					$pros = $db->val($sql_node_pro);
					$val[0][$d['_type']][$d['id']][1][$n['_order']][1] = array($pros['_head'],$pros['_potential'],$pros['_int'],$pros['_com'],$pros['_pol'],$pros['_lv']);
					$sql_node_army = "select units_id,units_num from aw_dungeon_army where node_id='{$n['id']}'";
					$armys = $db->vals($sql_node_army,true);
					foreach($armys as $a){
						$val[0][$d['_type']][$d['id']][1][$n['_order']][2][] = array($a['units_id'],$a['units_num']);
					}
					$sql_reward = "select _type,_value,_num,_percent from aw_dungeon_reward where node_id='{$n['id']}'";
					$rewards = $db->vals($sql_reward,true);
					foreach($rewards as $r){
						$val[0][$d['_type']][$d['id']][1][$n['_order']][3][] = array($r['_type'],$r['_value'],$r['_num'],$r['_percent']);
					}
					
					$val[1][$n['id']] = array($d['_type'],$d['id'],$n['_order']);
				}
				$sql_tech = "select tech_id,tech_lv from aw_dungeon_tech where dungeon_id='{$d['id']}'";
				$techs = $db->vals($sql_tech,true);
				foreach($techs as $t){
					$val[0][$d['_type']][$d['id']][2][$t['tech_id']] = array($t['tech_lv']);
				}
			}
			$sql_map = "select id,cid,dungeon_id,node_id,dungeon_type from aw_dungeon_map";
			$maps = $db->vals($sql_map,true);
			foreach ($maps as $m){
				$val[2][$m['cid']][0][$m['dungeon_type']][] = array($m['id'],$m['dungeon_id'],$m['node_id']);
			}
			$cache->set($key, $val);
		}
		return $val;
	}
	
	function aw_dungeon_sql(){
		return CACHE_KEY_DUNGEON;
	}
	
	define("CACHE_KEY_ELECTION","select cid,_nickname,agree_count,_people,_time,_army,_gov,_order,_lv from aw_election where _country=");
	/**
	 * 根据国家id返回国家所有参选人
	 * $val[0]['cid'] = array("_nickname","agree_count","_peop","_time","_army","_gov","_order","_lv")
	 * 根据参选人角色名返回角色ID
	 * $val[1]['nickname'] = $cid;
	 * 根据票数排序，一小时由定时器排序一次。
	 * $val[2][] = $cid;
	 * @param unknown_type $country
	 * @param unknown_type $cas
	 */
	function aw_election($country,&$cas=null){
		global $cache;
		$sql = aw_election_sql($country);
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			$elections = $db->vals($sql,true);
			foreach($elections as $v){
				$val[0][$v['cid']] = array($v['_nickname'],$v['agree_count'],$v['_people'],$v['_time'],$v['_army'],$v['_gov'],$v['_order'],$v['_lv']);
				$val[1][$v['_nickname']] = $v['cid'];
				$val[2][] = $v['cid'];
			}
			$cache->set($key, $val);
		}
		return $val;
	}
	function aw_election_sql($country){
		return CACHE_KEY_ELECTION."'{$country}' order by _order";
	}
	/**
	 * 查看投票记录
	 * $val['cid'] = $_country;
	 * @var unknown_type
	 */
	define("CACHE_ELECTION_MAP","select cid,_country from aw_election_map");
	function aw_election_map(&$cas=null){
		global $cache;
		$sql = aw_election_map_sql();
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			$country = $db->vals($sql,true);
			foreach($country as $v){
				$val[$v['cid']] = $v['_country'];
			}
			$cache->set($key, $val);
		}
		return $val;
	}
	function aw_election_map_sql(){
		return CACHE_ELECTION_MAP;
	}
	define("CACHE_KEY_FIGHT_HISTORY","select id,atker,defer,winer,_report,_date,_type,_lock,_scene,_fight_id from aw_fight_history where _owner=");
	/**
	 * $val[0]['id'] = array("id","atker","defer","winer","_report","_date","_type","_lock","_scene","_fight_id");
	 * 0 = atker	<br>
	 * 1 = defer	<br>
	 * 2 = winer	<br>
	 * 3 = _report	<br>
	 * 4 = _date	<br>
	 * 5 = _type	<br>
	 * 6 = _lock	<br>
	 * 7 = _scene	<br>
	 * 8 = _fight_id	<br>
	 * 按战报记录分类
	 * $val[1]['_type'] = $id;
	 * 收藏记录
	 * $val[2][] = $id;
	 * 玩家战索引
	 * $val[3][] = $id;
	 * @param unknown_type $cid
	 * @param unknown_type $cas
	 * @return multitype:unknown
	 */
	function aw_fight_history($cid,&$cas=null){
		global $cache;
		$sql = aw_fight_history_sql($cid);
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			$history = $db->vals($sql,true);
			foreach($history as $h){
				$val[0][$h['id']] = array($h['atker'],$h['defer'],$h['winer'],$h['_report'],$h['_date'],$h['_type'],$h['_lock'],$h['_scene'],$h['_fight_id']);
				$val[1][$h['_type']][] = $h['id'];
				
				if($h['_lock']==1){
					$val[2][] = $h['id'];
				}
				if($h['_type']==1||$h['_type']==4){
					$val[3][] = $h['id'];
				}
			}
			$cache->set($key, $val);
		}
		return $val;
	}
	function aw_fight_history_sql($cid){
		return CACHE_KEY_FIGHT_HISTORY."'{$cid}' order by _date desc";
	}
	define("CACHE_KEY_FIGHT_RECORD","select id,cid,_time,_type,dungeon_id,target_cid,is_use,openNext from aw_fight_record");
	/**
	 * 战斗记录缓存
	 * $val[0]['id'][0] = array("cid","_time","_type","dungeon_id","target_cid","is_use","openNext");
	 * 0 = cid
	 * 1 = _time
	 * 2 = _type
	 * 3 = dungeon_id
	 * 4 = target_cid
	 * 5 = is_use
	 * 6 = openNext
	 * 攻方配兵记录
	 * $val[0]['id'][1][0][0][] = array("units_id","_num","_posx","_posy","type","atk","atk_air","def","def_air","hp","view","speed");
	 * 0 = units_id
	 * 1 = units_num
	 * 2 = posx
	 * 3 = posy
	 * 4 = type
	 * 5 = atk
	 * 6 = atk_air
	 * 7 = def
	 * 8 = def_air
	 * 9 = hp
	 * 10 = view
	 * 11 = speed
	 * 防方配兵记录
	 * $val[0]['id'][1][0][1][] = array("units_id","_num","_posx","_posy");
	 * 攻方飞弹设置
	 * $val[0]['id'][1][1][0][] = array("units_id","units_num","_type");
	 * 防方飞弹设置
	 * $val[0]['id'][1][1][0][] = array("units_id","units_num","_type");
	 * 以国家为索引返回国战队列
	 * $val[1]['target_country'][] = array("fight_id");
	 * 玩家所有战斗
	 * $val[2]['cid'][] = $fight_id
	 * 玩家正被打
	 * $val[3]['cid][0] = $fight_id
	 * 玩家所有被打
	 * $val[3]['cid][1][] = $fight_id
	 * @param unknown_type $cas
	 */
	function aw_fight_record(&$cas=null){
		global $cache;
		$sql = aw_fight_record_sql();
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			$fights = $db->vals($sql,true);
			
			$aw_units = aw_units();
			
			foreach($fights as $f){
				$val[0][$f['id']][0] = array($f['cid'],$f['_time'],$f['_type'],$f['dungeon_id'],$f['target_cid'],$f['is_use'],$f['openNext']);
				if($f['_type']==2&&$f['openNext']==0){
					//type为2时，target_cid表示目标国id
					$val[1][0][$f['target_cid']][] = $f['id'];
					$val[1][1][$f['dungeon_id']][] = $f['id'];
				}
				//横坐标大于3的为防方，小于3的为攻方
				$sql_atk_army = "select units_id,_num,_posx,_posy,_type,_atk,_atk_air,_def,_def_air,_hp,_view,_speed from aw_fight_army where fight_id='{$f['id']}'";
				$atk_army = $db->vals($sql_atk_army,true);
				foreach($atk_army as $aa){
					$attribute = $aw_units[0][$aa['units_id']][2];
					$atk = $attribute[0];
					$def = $attribute[1];
					$atk_air = $attribute[2];
					$def_air = $attribute[3];
					$hp = $attribute[4];
					$view = $attribute[6];
					$speed = $attribute[7];
					
					if($aa['_posx']<3){
						$val[0][$f['id']][1][0][0][] = array($aa['units_id'],$aa['_num'],$aa['_posx'],$aa['_posy'],$aa['_type'],$atk,$atk_air,$def,$def_air,$hp,$view,$speed);
					}else{
						$val[0][$f['id']][1][0][1][] = array($aa['units_id'],$aa['_num'],$aa['_posx'],$aa['_posy'],$aa['_type'],$atk,$atk_air,$def,$def_air,$hp,$view,$speed);
					}
					
				}
				$val[2][$f['cid']][] = $f['id'];
				if($f['_type']==1||$f['_type']==4){
// 					if($f['id']<$val[3][$f['target_cid']][0]){
// 						$val[3][$f['target_cid']][0] = $f['id'];
// 					}
					$val[3][$f['target_cid']][1][] = $f['id'];
				}
				$sql_fly = "select cid,units_id,units_num,_type from aw_fight_fly where fight_id='{$f['id']}'";
				$fly = $db->vals($sql_fly,true);
				foreach($fly as $ff){
					if($ff['cid']==$f['cid']){
						$val[0][$f['id']][1][1][0][] = array($ff['units_id'],$ff['units_num'],$ff['_type']);
					}else{
						$val[0][$f['id']][1][1][1][] = array($ff['units_id'],$ff['units_num'],$ff['_type']);
					}
				}
			}
			$cache->set($key, $val);
		}
		return $val;
	}
	function aw_fight_record_sql(){
		return CACHE_KEY_FIGHT_RECORD;
	}
	define("CACHE_KEY_ITEM","select id,_type,_price,_sell,_price_rmb,mall_sell,_percent,market_percent,_temp_num,_quality,_name from aw_item order by _percent,id");
	/**
	 * 道具信息
	 * $val[0]['id'] = array("_type","_price","_sell","_price_rmb","mall_sell","_percent","market_percent","_temp_num","_quality","_name");
	 * 0 = "_type",
	 * 1 = "_price",
	 * 2 = "_sell",
	 * 3 = "_price_rmb",
	 * 4 = "mall_sell",
	 * 5 = "_percent",
	 * 6 = "market_percent",
	 * 7 = "_temp_num",
	 * 8 = "_quality",
	 * 9 = "_name",
	 * 根据黑市商人刷新机率建立索引
	 * $val[1]['percent'][] = $id;
	 * 根据黑市刷新机率建立索引
	 * $val[2]['market_percent'][] = $id;
	 */
	function aw_item(){
		global $cache;
		$sql = CACHE_KEY_ITEM;
		$key = key_cache($sql);
		$valu = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			$items = $db->vals($sql,true);
			$val = array();
			foreach($items as $v){
				$val[0][$v['id']] = array($v["_type"],$v["_price"],$v["_sell"],$v["_price_rmb"],$v["mall_sell"],$v["_percent"],$v["market_percent"],$v["_temp_num"],$v["_quality"],$v["_name"]);
				if($v['_percent']>0){
					$val[1][$v['_percent']][] = $v['id'];
				}
				if($v['market_percent']>0){
					$val[2][$v['market_percent']][] = $v['id'];
				}
				
			}
			$value = json_encode($val);
			$cache->set($key,$value);
		}
		if(!empty($valu)){
			$val = json_decode($valu);
			$val = object_to_array($val);
// 			$val = (array)$val;
		}
		return $val;
	}
	
	
	
	define("CACHE_KEY_MAIL","select id,_has_attachment,_type,_title,_content,_wdate,_read,_sender_cid,_cost,_sell_money,_sell_rmb,_is_owner,_sender_name from aw_mail where cid=");
	define("CACHE_KEY_MAIL_ATTACHMENT","select id,_type,_num,_value from aw_mail_attachment where mail_id=");
	define("CACHE_KEY_MAIL_SENDSELL","select id,cid from aw_mail where _is_owner=0 and _sender_cid=");
	/**
	 * 根据角色ID返回角色邮件
	 * $val[0]['_is_owner']['id'][0] = array("_has_attachment","_type","_title","_content","_wdate","_read","_sender_cid","_cost","_sell_money","_sell_rmb");
	 * 0=>_has_attachment    <br>
	 * 1=>_type    <br>
	 * 2=>_title    <br>
	 * 3=>_content    <br>
	 * 4=>_wdate    <br>
	 * 5=>_read    <br>
	 * 6=>_sender_cid    <br>
	 * 7=>_cost    <br>
	 * 8=>_sell_money    <br>
	 * 9=>_sell_rmb    <br>
	 * 10=>_sender_name    <br>
	 * 如果邮件包含附件，结构如下
	 * $val[0]['_is_owner']['id'][1]['attach_id'] = array("_type","_value","_num")
	 * 索引：根据邮件ID查看邮件拥有状态(只包含送达的)
	 * $val[1]['id'] = $_is_owner;
	 * @param int $cid
	 * @param int $cas
	 */
	function aw_mail($cid,&$cas=null){
		global $cache;
		$sql = aw_mail_sql($cid);
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			$mails = $db->vals($sql,true);
			$val = array();
			$_time = time() - TIME_FOR_ATTACHMENT_ARRIVED;
			foreach($mails as $m){
				if($m['_is_owner']==2&&$m['_wdate']<=$_time){
					$m['_is_owner'] = 0;
				}
				$val[0][$m['_is_owner']][$m['id']][0] = array($m['_has_attachment'],$m['_type'],$m['_title'],$m['_content'],$m['_wdate'],$m['_read'],$m['_sender_cid'],$m['_cost'],$m['_sell_money'],$m['_sell_rmb'],$m['_sender_name']);
				$val[1][$m['id']] = $m['_is_owner'];
				if($m['_has_attachment']==1){
					$att_sql = CACHE_KEY_MAIL_ATTACHMENT."'{$m['id']}'";
					$attach = $db->vals($att_sql,true);
					foreach($attach as $a){
						$val[0][$m['_is_owner']][$m['id']][1][$a['id']] = array($a['_type'],$a['_value'],$a['_num']);
					}
				}
				
				
			}
			$cache->set($key, $val);
		}
		return $val;
	}
	
	function aw_mail_sendsell($cid,&$cas=null){
		global $cache;
		$sql = aw_mail_sendsell_sql($cid);
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			
			$sendsell = $db->vals($sql,true);
			$val = array();
			foreach($sendsell as $m){
// 				$val[$m['cid']][] = $m['id'];
				$val[0][$m['id']] = $m['cid'];
			}
			$cache->set($key, $val);
		}
		return $val;
	}
	function aw_mail_sendsell_sql($cid){
		return CACHE_KEY_MAIL_SENDSELL."'{$cid}' order by _wdate desc";
	}
	
	function aw_mail_sql($cid){
		return CACHE_KEY_MAIL."'{$cid}' order by _wdate desc";
	}
	define("CACHE_KEY_MARK_COUNTRY_MAP","select id,action_type,_value,_time,_death,_country from aw_mark_country_map");
	/**
	 * 国家标记
	 * $val[0]['_country']['action_type']['_value'][] = array("id","_time","_death");
	 */
	function aw_mark_country_map(&$cas=null){
		global $cache;
		$sql = aw_mark_country_map_sql();
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			$vals = $db->vals($sql,true);
			$val = array();
			foreach($vals as $v){
				$val[0][$v['_country']][$v['action_type']][$v['_value']][] = array($v['id'],$v['_time'],$v['_death']);
// 				$val[1][$v['id']] = array($v['action_type'],$v['_value']);
			}
			$cache->set(md5($sql),$val);
		}
		return $val;
	}
	function aw_mark_country_map_sql(){
		return CACHE_KEY_MARK_COUNTRY_MAP;
	}
	define("CACHE_KEY_MARK_MAP","select id,action_type,_value,_time,_death from aw_mark_map where cid=");
	/**
	 * 根据用户ID返回用户标记
	 * 因为偷取资源可能会出现action_type和_value相同的情况，所以以一个数组存这个数据，一般情况不存在多条记录的，都只取第0条记录
	 * $val[0]['action_type']['_value'][] = array("id","_time","_death");
	 */
	function aw_mark_map($cid,&$cas=null){
		global $cache;
		$sql = aw_mark_map_sql($cid);
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			$vals = $db->vals($sql,true);
			$val = array();
			foreach($vals as $v){
				$val[0][$v['action_type']][$v['_value']][] = array($v['id'],$v['_time'],$v['_death']);
// 				$val[1][$v['id']] = array($v['action_type'],$v['_value']);
			}
			$cache->set(md5($sql),$val);
		}
		return $val;
	}
	function aw_mark_map_sql($cid){
		return CACHE_KEY_MARK_MAP."'{$cid}'";
	}
	
	define("CACHE_KEY_MISSION","select id,_type,_order from aw_mission order by _type,_order");
	function aw_mission(){
		global $cache;
		$sql = CACHE_KEY_MISSION;
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			
			$missions = $db->vals($sql,true);
			foreach($missions as $m){
				$val[0][$m['_type']][$m['id']][0] = array($m['_order']);
				
				$sql_need = "select _type,_value,_num from aw_mission_need where mission_id='{$m['id']}'";
				$need = $db->vals($sql_need,true);
				foreach($need as $n){
					$val[0][$m['_type']][$m['id']][1][] = array($n['_type'],$n['_value'],$n['_num']);
				}
				
				$sql_reward = "select _type,_value,_num from aw_mission_reward where mission_id='{$m['id']}'";
				$reward = $db->vals($sql_reward,true);
				foreach($reward as $r){
					$val[0][$m['_type']][$m['id']][2][] = array($r['_type'],$r['_value'],$r['_num']);
				}
				
				$val[1][$m['id']] = $m['_type'];
			}
			$cache->set($key, $val);
		}
		return $val;
	}
	
	define("CACHE_KEY_MISSIONU","select id,_type,cid,_wdate,_status,index1,index2,index3,_area,_area_value from aw_missionu order by _wdate");
	/**
	 * $val[0]['_type'][0]['id'][0] = array("cid","_wdate","_status","index1","index2","index3","_area","_area_value")
	 * 0 = cid	<br>
	 * 1 = _wdate	<br>
	 * 2 = _status	<br>
	 * 3 = index1	<br>
	 * 4 = index2	<br>
	 * 5 = index3	<br>
	 * 6 = _area	<br>
	 * 7 = _area_value	<br>
	 * 任务需要need
	 * $val[0]['_type'][0]['id'][1] = array("_type","_value","_num")
	 * 任务奖励reward
	 * $val[0]['_type'][0]['id'][2] = array("_type","_value","_num")
	 * 索引，根据用户名返回用户发布的任务ID
	 * $val[0]['_type'][1]['cid'][] = $id;
	 * 索引，根据任务ID返回任务类型
	 * $val[1]['id'] = $missionu_type
	 * @param unknown_type $cas
	 * @return multitype:
	 */
	function aw_missionu(&$cas=null){
		global $cache;
		$sql = aw_missionu_sql();
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			$missionu = $db->vals($sql,true);
			foreach($missionu as $m){
				$val[0][$m['_type']][0][$m['id']][0] = array($m['cid'],$m['_wdate'],$m['_status'],$m['index1'],$m['index2'],$m['index3'],$m['_area'],$m['_area_value']);
				$sql_need = "select _type,_value,_num from aw_missionu_need where mission_id='{$m['id']}'";
				$n = $db->val($sql_need);
				$sql_reward = "select _type,_value,_num from aw_missionu_reward where mission_id='{$m['id']}'";
				$r = $db->val($sql_reward);
				$needArr = array();
				if(!empty($n)){
					$needArr = array($n['_type'],$n['_value'],$n['_num']);
				}
				$rewardArr = array();
				if(!empty($r)){
					$rewardArr = array($r['_type'],$r['_value'],$r['_num']);
				}
				//need
				$val[0][$m['_type']][0][$m['id']][1] = $needArr;
				//reward
				$val[0][$m['_type']][0][$m['id']][2] = $rewardArr;
				
// 				if($m['_type']==0){
// 					//need
// 					$val[0][$m['_type']][0][$m['id']][1] = $needArr;
// 					//reward
// 					$val[0][$m['_type']][0][$m['id']][2] = $rewardArr;
// 				}else{
// 					//need
// 					$val[0][$m['_type']][0][$m['id']][1] = $rewardArr;
// 					//reward
// 					$val[0][$m['_type']][0][$m['id']][2] = $needArr;
// 				}
				
				$val[0][$m['_type']][1][$m['cid']][] = $m['id'];
				
				$val[1][$m['id']] = $m['_type'];
			}
			$cache->set($key, $val);
		}
		return $val;
	}
	
	function aw_missionu_sql(){
		return CACHE_KEY_MISSIONU;
	}
	define("CACHE_KEY_ONLINELIST","select id,cid,session_key,_lastaction,_nickname,_country,_continents from aw_onlinelist");
	/**
	 * 在线用户列表
	 * 0=id
	 * 1=session_key
	 * 2=_lastaction
	 * 3=_nickname
	 * 4=_country
	 * 5=_continents
	 * @param unknown_type $cas
	 * @return multitype:multitype:unknown
	 */
	function aw_onlinelist(&$cas=null){
		global $cache;
		$sql = aw_onlinelist_sql();
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			
			$list = $db->vals($sql,true);
			$val = array();
			foreach($list as $v){
				$val[$v['cid']] = array($v["id"],$v["session_key"],$v["_lastaction"],$v["_nickname"],$v["_country"],$v["_continents"]);
			}
			$cache->set($key, $val);
		}
		return $val;
	}
	function aw_onlinelist_sql(){
		return CACHE_KEY_ONLINELIST;
	}
	define("CACHE_KEY_ONLINE_REWARD_MAP","select id,cid,_time,_count from aw_online_reward_map");
	/**
	 * $val[0]['_count'][] = array("_type","_value","_num","_chance");
	 * $val[1]['cid'][0] = array("_time","_count","id");
	 * @param unknown_type $cas
	 */
	function aw_online_reward_map(&$cas=null){
		global $cache;
		$sql = aw_online_reward_map_sql();
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			
			$list = $db->vals($sql,true);
			foreach($list as $l){
				$val[1][$l['cid']][0] = array($l['_time'],$l['_count'],$l['id']);
			}
			
			$sql_reward = "select _type,_value,_num,_chance,_need_count from aw_online_reward order by _need_count";
			$reward = $db->vals($sql_reward,true);
			foreach($reward as $r){
				$val[0][$r['_need_count']][] = array($r['_type'],$r['_value'],$r['_num'],$r['_chance']);
			}
			$cache->set($key, $val);
		}
		return $val;
	}
	function aw_online_reward_map_sql(){
		return  CACHE_KEY_ONLINE_REWARD_MAP;
	}
	define("CACHE_KEY_PROFESSOR","select id,_job,_potential,_int,_com,_pol,_head,_type,_rand,_price,_rmb from aw_professor order by _rand");
	/**
	 * 专家配置缓存
	 * $val[0]['id'] = array("_job","_potential","_int","_com","_pol","_head","_type","_rand","_price","_rmb");
	 * 0 = _job	<br>
	 * 1 = _potential	<br>
	 * 2 = _int	<br>
	 * 3 = _com	<br>
	 * 4 = _pol	<br>
	 * 5 = _head	<br>
	 * 6 = _type	<br>
	 * 7 = _rand	<br>
	 * 8 = _price	<br>
	 * 9 = _rmb	<br>
	 * 专家刷新最大rand
	 * $val[1] = $max_rand
	 * @return multitype:unknown
	 */
	function aw_professor(){
		global $cache;
		$sql = CACHE_KEY_PROFESSOR;
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			$professor = $db->vals($sql,true);
			foreach($professor as $p){
				$val[0][$p['id']] = array($p['_job'],$p['_potential'],$p['_int'],$p['_com'],$p['_pol'],$p['_head'],$p['_type'],$p['_rand'],$p['_price'],$p['_rmb']);
				if($p['_rand']>$val[1])$val[1] = $p['_rand'];
			}
			$cache->set($key, $val);
		}
		return $val;		
	}
	define("CACHE_KEY_PROFESSOR_MAP","select m.id,m.professor_name,m._job,m._head,m._potential,m._int,m._com,m._pol,m._work_building,m._exp,m._lv,m._action_time,m._change_time,m._lock+0 as _lock,m._is_command,t._base_int,t._base_com,t._base_pol,t._int as _grow_int,t._com as _grow_com,t._pol as _grow_pol,t.id as tid  from aw_professor_map m left join aw_professor_grow_temp t on t.pro_id=m.id where m.cid=");
	/**
	 * 根据角色id返回用户专家信息
	 * $val[0]['_job']['id'][0] = array(professor_name,_job,_head,_potential,_int,_com,_pol,_work_building,_exp,_lv,_action_time,_change_time,_lock,_is_command);
	 * 0=>professor_name    <br>
	 * 1=>_job    <br>
	 * 2=>_head    <br>
	 * 3=>_potential    <br>
	 * 4=>_int    <br>
	 * 5=>_com    <br>
	 * 6=>_pol    <br>
	 * 7=>_work_building    <br>
	 * 8=>_exp    <br>
	 * 9=>_lv    <br>
	 * 10=>_action_time    <br>
	 * 11=>_change_time    <br>
	 * 12=>_lock    <br>
	 * 13=>_is_command    <br>
	 * $val[0]['_job']['id'][1] = array("_base_int","_base_com","_base_pol","_grow_int","_grow_com","_grow_pol","temp_id");
	 * $val[0]['_job']['id'][2] = array("_int","_com","_pol","grow_id");
	 * 索引，根据专家主键ID返回专家职业
	 * $val[1]['id'] = $_job
	 * 专家招募列表
	 * $val[2]['id'] = array("professor_id","professor_name","_price");
	 * 指挥官id
	 * $val[3] = $id
	 * @param  $cid
	 * @param  $cas
	 */
	function aw_professor_map($cid,&$cas=null){
		global $cache;
		$sql = aw_professor_map_sql($cid);
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			$professors = $db->vals($sql,true);
			$val = array();
			foreach($professors as $p){
				$val[0][$p['_job']][$p['id']][0] = array($p['professor_name'],$p['_job'],$p['_head'],$p['_potential'],$p['_int'],$p['_com'],$p['_pol'],$p['_work_building'],$p['_exp'],$p['_lv'],$p['_action_time'],$p['_change_time'],$p['_lock'],$p['_is_command']);
				$val[0][$p['_job']][$p['id']][1] = array($p['_base_int'],$p['_base_com'],$p['_base_pol'],$p['_grow_int'],$p['_grow_com'],$p['_grow_pol'],$p['tid']);
				$map = $db->val("select id,_int,_com,_pol from aw_professor_grow_map where pro_id='{$p['id']}'");
				if(!empty($map)){
					$val[0][$p['_job']][$p['id']][2] = array($map['_int'],$map['_com'],$map['_pol'],$map['id']);
				}
				$val[1][$p['id']] = $p['_job'];
				if($p['_is_command']==1){
					$val[3] = $p['id'];
				}
			}
			$sql_temp = "select id,professor_id,professor_name,_price,beRecruit from aw_professor_temp where cid='{$cid}'";
			$professor_temp = $db->vals($sql_temp);
			$aw_professor = aw_professor();
			foreach($professor_temp as $temp){
				$val[2][$temp['id']][0] = array($temp['professor_id'],$temp['professor_name'],$temp['_price'],$temp['beRecruit']);
				$val[2][$temp['id']][1] = $aw_professor[0][$temp['professor_id']];
			}
			$cache->set($key, $val);
		}
		return $val;
	}
	function aw_professor_map_sql($cid){
		return CACHE_KEY_PROFESSOR_MAP."'{$cid}'";
	}
	define("CACHE_KEY_RESOURCE", "select t.id tid,t.build_id,t._res,t._stole,t._lastmake,t._hasMake,g.id gid,g.grow_value from aw_resource_temp t left join aw_resource_grow g on g.building_id=t.build_id where t.cid=");
	/**
	 * 根据角色ID返回角色资源
	 * $val[0]['bld_id']['building_id'] = array("tid","_res","_stole","_lastmake","_hasMake","gid","grow_value");
	 * 0->tid	aw_resource_temp表的id	<br>
	 * 1->_res	<br>
	 * 2->_stole	<br>
	 * 3->_lastmake	<br>
	 * 4->_hasMake	<br>
	 * 5->gid	aw_resource_grow表的id	<br>
	 * 6->grow_value	<br>
	 * 索引(如果资源已经成熟并刷新了资源状态，可将lastmake设为0，以免重复验证)
	 * $val[1]['building_id'] = $_lastmake;
	 */
	function aw_resource($cid,&$cas=null){
		global $cache;
		$sql = aw_resource_sql($cid);
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			
			$res = $db->vals($sql,true);
			$val = array();
			$aw_building_map = aw_building_map($cid);
			$buildings = $aw_building_map[1];
			foreach($res as $v){
				$bld_id = $buildings[$v['build_id']];
				//如果资源建筑已经成熟，设置为成熟状态
				if(time()-$v['_lastmake']>RESOURCE_MAKE_TIME){
					$v['_hasMake']=1;
					$v['_res'] = floor($v['grow_value']*0.8);
					$v['_stole'] = $v['grow_value'] -$v['_res'];
				}
				$val[0][$bld_id][$v['build_id']] = array($v['tid'],$v['_res'],$v['_stole'],$v['_lastmake'],$v['_hasMake'],$v['gid'],$v['grow_value']);
				//如果资源已经成熟并刷新了资源状态，可将lastmake设为0，以免重复验证
				if($v['_hasMake']==1)$v['_lastmake'] = 0;
				$val[1][$v['build_id']] = $v['_lastmake'];
			}
			$cache->set($key, $val);
		}
		return $val;
	}
	define("CACHE_KEY_RESOURCE_INDEX","select cid,build_id from aw_resource_temp");
	function aw_resource_index(&$cas=null){
		global $cache;
		$sql = CACHE_KEY_RESOURCE_INDEX;
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			$index = $db->vals($sql,true);
			$val = array();
			foreach($index as $i){
				$val[$i['build_id']] = $i['cid'];
			}
			$cache->set($key, $val);
		}
		return $val;
	}
	function aw_resource_sql($cid){
		return CACHE_KEY_RESOURCE."'{$cid}'";
	}
	
	define("CACHE_KEY_SERVER_CONFIG","select k_get_stone,k_get_gold,k_get_iron,k_get_re,k_add,stone_min,stone_max,gold_min,gold_max,iron_min,iron_max,re_min,re_max from aw_server_config");
	/**
	 * $val = array("k_get_stone","k_get_gold","k_get_iron","k_get_re","k_add","stone_min","stone_max","gold_min","gold_max","iron_min","iron_max","re_min","re_max");
	 * 0=k_get_stone
	 * 1=k_get_gold
	 * 2=k_get_iron
	 * 3=k_get_re
	 * 4=k_add
	 * 5=stone_min
	 * 6=stone_max
	 * 7=gold_min
	 * 8=gold_max
	 * 9=iron_min
	 * 10=iron_max
	 * 11=re_min
	 * 12=re_max
	 */
	function aw_server_config(&$cas=null){
		global $cache;
		$sql = aw_server_config_sql();
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			$v = $db->val($sql);
			$val = array($v["k_get_stone"],$v["k_get_gold"],$v["k_get_iron"],$v["k_get_re"],$v["k_add"],$v["stone_min"],$v["stone_max"],$v["gold_min"],$v["gold_max"],$v["iron_min"],$v["iron_max"],$v["re_min"],$v["re_max"]);
			$cache->set($key, $val);
		}
		return $val;
	}
	
	function aw_server_config_sql(){
		return CACHE_KEY_SERVER_CONFIG;
	}
	define("CACHE_KEY_STORAGE", "select id,item_id,item_num from aw_storage where cid=");
	/**
	 * 根据角色id返回用户背包信息
	 * @param int $cid 角色ID
	 * @param int $cas 这个参数（这个参数在函数定义中是引用参数，用来传出元素的版本标记，原理 可以查阅乐观锁资料）将会包含该元素的CAS标记值
	 * $val[0]['type']['item_id'][0] = array(0=>"id",1=>"item_num");
	 */
	function aw_storage($cid,&$cas=null){
		global $cache;
		$sql = aw_storage_sql($cid);
		$key = key_cache($sql);
		$valu = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			$storage = $db->vals($sql,true);
			$val = array();
			$aw_item = aw_item();
			foreach($storage as $s){
				$type = $aw_item[0][$s['item_id']][0];
				$val[0][$type][$s['item_id']][0] = array($s['id'],$s['item_num']);
			}
			$value = json_encode($val);
			$cache->set($key,$value);
		}
		if(!empty($valu)){
			$val = json_decode($valu);
			$val = object_to_array($val);
		}
		return $val;
	}
	/**
	 * 初始化缓存(只读取数据库数据然后存入缓存，提供给缓存方法使用)<br>
	 * 当且仅当服务器启动时（一般在定时器开启时）调用。如果某个用户的数据被清理，使用aw_storage(cid)方法初始化<br>
	 * 取数据的key为字符串：select id,item_id,item_num from aw_storage where cid='{$cid}' 处理过后的数据<br>
	 * 取到的数据格式：$val['type']['item_id'] = array(0=>"id",1=>"item_num");
	 */
	function aw_storage_init(){
		$db = new d5db();
		$db->connect();
	
		$cids = $db->vals("select cid from aw_storage group by cid",true);
		$aw_item = aw_item();
		foreach($cids as $cid){
			$sql = aw_storage_sql($cid['cid']);
			$key = key_cache($sql);
			$val = $cache->get($key);
			if($cache->getResultCode() == Memcached::RES_NOTFOUND){
				$storage = $db->vals($sql,true);
				$val = array();
				foreach($storage as $s){
					$type = $aw_item[0][$s['item_id']][0];
					$val[$type][$s['item_id']] = array($s['id'],$s['item_num']);
				}
				$cache->set($key,$val);
			}
		}
	
	}
	function aw_storage_sql($cid){
		return CACHE_KEY_STORAGE."'{$cid}'";
	}
	
	define("CACHE_KEY_SNS","select id,sns_cid,_type from aw_sns where cid=");
	function aw_sns($cid,&$cas=null){
		global $cache;
		$sql = aw_sns_sql($cid);
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			$snss = $db->vals($sql,true);
			$val = array();
			foreach($snss as $s){
				$val[0][$s['_type']][$s['id']] = $s['sns_cid'];
				$val[1][$s['id']] = $s['_type'];
			}
			$cache->set($key, $val);
		}
		return $val;
	}
	function aw_sns_sql($cid){
		return CACHE_KEY_SNS."'{$cid}'";
	}
	define("CACHE_KEY_TECH","select id,tech_type from aw_tech");
	/**
	 * 科技类型
	 * $val[0]['id'][0] = array("tech_type");
	 * 科技基本消耗
	 * $val[0]['id'][1] = array("cost_rice","cost_wood","cost_stone","cost_oil","cost_time","cost_rmb","cost_res_k","cost_time","cost_money");
	 * 0 = cost_rice
	 * 1 = cost_money
	 * 2 = cost_time
	 * 3 = cost_wood
	 * 4 = cost_stone
	 * 5 = cost_oil
	 * 6 = cost_rmb
	 * 7 = cost_res_k
	 * 8 = cost_time_k
	 * 科技学习需求
	 * $val[0]['id'][2][] = array("type","value","num");
	 * 科技效果等级分布
	 * $val[0]['id'][3]['tech_lv'][] = array("effect_id","effect_value","effect_num");
	 * 科技最大允许学习等级(由科技效果决定)
	 * $val[0]['id'][4] = $tech_max_lv;
	 * @return multitype:unknown
	 */
	function aw_tech(){
		global $cache;
		$sql = CACHE_KEY_TECH;
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			$techs = $db->vals($sql,true);
			foreach($techs as $t){
				$val[0][$t['id']][0] = array($t['tech_type']);
				$sql_attribute = "select cost_rice,cost_wood,cost_stone,cost_oil,cost_time,cost_rmb,cost_res_k,cost_time_k,cost_money from aw_tech_attribute where tech_id='{$t['id']}'";
				$attr = $db->val($sql_attribute);
				$val[0][$t['id']][1] = array($attr['cost_rice'],$attr['cost_money'],$attr['cost_time'],$attr['cost_wood'],$attr['cost_stone'],$attr['cost_oil'],$attr['cost_rmb'],$attr['cost_res_k'],$attr['cost_time_k']);
			}
			$sql_need = "select tech_id,_type,_value,_num from aw_tech_need";
			$need = $db->vals($sql_need,true);
			foreach($need as $n){
				$val[0][$n['tech_id']][2][] = array($n['_type'],$n['_value'],$n['_num']);
			}
			$sql_effect = "select tech_id,tech_lv,effect_id,effect_value,effect_num from aw_tech_effect";
			$effects = $db->vals($sql_effect,true);
			foreach($effects as $e){
				$val[0][$e['tech_id']][3][$e['tech_lv']][] = array($e['effect_id'],$e['effect_value'],$e['effect_num']);
				if($e['tech_lv']>$val[0][$e['tech_id']][4]){
					$val[0][$e['tech_id']][4] = $e['tech_lv'];
				}
			}
			$cache->set($key, $val);
		}
		return $val;
	}
	define("CACHE_KEY_TECH_MAP","select tech_id,tech_lv from aw_tech_map where cid=");
	/**
	 * $val[0]['tech_id'][0] = array("tech_lv")
	 * $val[1]['build_id'] = array("tech_id","tech_lv","start_time","end_time")
	 * @param unknown_type $cid
	 * @param unknown_type $cas
	 * @return multitype:unknown
	 */
	function aw_tech_map($cid,&$cas=null){
		global $cache;
		$sql = aw_tech_map_sql($cid);
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			$techs = $db->vals($sql,true);
			foreach($techs as $t){
				$val[0][$t['tech_id']][0] = array($t['tech_lv']);
			}
			$sql_cd = "select build_id,tech_id,tech_lv,start_time,end_time from aw_tech_cd where cid='{$cid}'";
			$cds = $db->vals($sql_cd,true);
			foreach($cds as $c){
				$val[1][$c['build_id']] = array($c['tech_id'],$c['tech_lv'],$c['start_time'],$c['end_time']);
			}
			$cache->set($key, $val);
		}
		return $val;
	}
	function aw_tech_map_sql($cid){
		return CACHE_KEY_TECH_MAP."'{$cid}'";
	}
	define("CACHE_KEY_TIME_NEED","select time_id,_type,_value,_num from aw_time_need");
	/**
	 * 时代升级配置缓存
	 * $val[0]['time_id'][0][] = array("_type","_value","_num");
	 * 最大时代
	 * $val[1] = $max_time;
	 */
	function aw_time_need(){
		global  $cache;
		$sql = CACHE_KEY_TIME_NEED;
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			$needs = $db->vals($sql,true);
			$max = 0;
			foreach($needs as $n){
				$val[0][$n['time_id']][0][] = array($n['_type'],$n['_value'],$n['_num']);
				if($n['time_id']>$max)$max = $n['time_id'];
			}
			$val[1] = $max;
			$cache->set($key, $val);
		}
		return $val;
	}

	define("CACHE_KEY_UNITS","select id,_type,_space,_one,_capacity,_need_capacity,_cruise_time,_order,_renew_type,_can_train,_name from aw_units");
	/**
	 * 单位信息
	 * $val[0]['id'][0] = array("_type","_space","_one","_capacity","_need_capacity","_cruise_time","_order","_renew_type","_can_train","_name");
	 * 0 = _type
	 * 1 = _space
	 * 2 = _one
	 * 3 = _capacity
	 * 4 = _need_capacity
	 * 5 = _cruise_time
	 * 6 = _order
	 * 7 = _renew_type
	 * 8 = _can_train
	 * 9 = _name
	 * 单位训练基本消耗
	 * $val[0]['id'][1] = array("cost_rice","cost_wood","cost_stone","cost_oil","cost_time","one_can_train");
	 * 0 = cost_rice
	 * 1 = cost_Wood
	 * 2 = cost_stone
	 * 3 = cost_oil
	 * 4 = cost_time
	 * 5 = one_can_train
	 * 6 = cost_money
	 * 单位战斗属性
	 * $val[0]['id'][2] = array("_atk","_def","_atk_air","_def_air","_hp","_atk_area","_view","_speed","_atk_speed");
	 * 0 = _atk
	 * 1 = _def
	 * 2 = _atk_air
	 * 3 = _def_air
	 * 4 = _hp
	 * 5 = _atk_area
	 * 6 = _view
	 * 7 = _speed
	 * 8 = _atk_speed
	 * 单位训练前置条件
	 * $val[0]['id'][3][] = array("_type","_value","_num");
	 * 空间派遣需求
	 * $val[1]['units_id'][] = array("_type","_value","_num");
	 */
	function aw_units(){
		global $cache;
		$sql = CACHE_KEY_UNITS;
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			$units = $db->vals($sql,true);
			foreach($units as $u){
				$val[0][$u['id']][0] = array($u['_type'],$u['_space'],$u['_one'],$u['_capacity'],$u['_need_capacity'],$u['_cruise_time'],$u['_order'],$u['_renew_type'],$u['_can_train'],$u['_name']);
				$sql_attr = "select cost_rice,cost_wood,cost_stone,cost_oil,cost_time,one_can_train from aw_units_attribute where units_id='{$u['id']}'";
				$attribute = $db->val($sql_attr);
				$val[0][$u['id']][1] = array($attribute['cost_rice'],$attribute['cost_wood'],$attribute['cost_stone'],$attribute['cost_oil'],$attribute['cost_time'],$attribute['one_can_train']);
				$sql_fight = "select _atk,_def,_atk_air,_def_air,_hp,_atk_area,_view,_speed,_atk_speed from aw_units_fight_attribute where units_id='{$u['id']}'";
				$fight = $db->val($sql_fight);
				$val[0][$u['id']][2] = array($fight['_atk'],$fight['_def'],$fight['_atk_air'],$fight['_def_air'],$fight['_hp'],$fight['_atk_area'],$fight['_view'],$fight['_speed'],$fight['_atk_speed']);
				$sql_need = "select _type,_value,_num from aw_units_need where units_id='{$u['id']}'";
				$needs = $db->vals($sql_need,true);
				foreach($needs as $n){
					if($n['_type']==37){
						$val[0][$u['id']][1][6] = $n['_value'];
					}else{
						$val[0][$u['id']][3][] = array($n['_type'],$n['_value'],$n['_num']);
					}
				}
			}
			$sql_space_need = "select units_id,_type,_value,_num from aw_space_need";
			$space_need = $db->vals($sql_space_need,true);
			foreach($space_need as $v){
				$val[1][$v['units_id']][] = array($v['_type'],$v['_value'],$v['_num']);
			}
			$cache->set($key, $val);
		}
		return $val;
	}
	define("CACHE_KEY_UNITS_MAP","select id,units_id,units_num from aw_units_map where cid=");
	/**
	 * 按角色ID返回角色单位<br>
	 * $val[0]['units_id'] = array(0=>id,1=>units_num);<br>
	 * 数组第0个元素是数据库表里的id方便操作数据库
	 * 第1个元素是单位数量
	 * 单位CD列表
	 * $val[1]['build_id'] = array("units_id","units_num","start_time","end_time");
	 * 伤兵缓存
	 * $val[2]['renew_type']['id'] = array("units_id","units_num","death_time");
	 * 空间活动
	 * $val[3][0]['_type']['units_id'][] = array("id","units_num","_nickname","start_time","end_time");
	 * $val[3][1]['id'] = array("_type","units_id");
	 * 伤兵索引
	 * $val[4]['id'] = $renew_type;
	 * 运输中的单位
	 * $val[5][] = array($units_id,$units_num,$arrive,$id);
	 * @param int $cid
	 * @param int $cas 引用参数，传递出数据的版本号
	 */
	function aw_units_map($cid,&$cas=null){
		global $cache;
		$sql = aw_units_map_sql($cid);
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			$units = $db->vals($sql,true);
			$val = array();
			foreach($units as $v){
				$val[0][$v['units_id']] = array($v['id'],$v['units_num']);
			}
			$sql_cd = "select build_id,units_id,units_num,start_time,end_time from aw_units_cd_map where cid='{$cid}'";
			$cs = $db->vals($sql_cd,true);
			foreach($cs as $c){
				$val[1][$c['build_id']] = array($c['units_id'],$c['units_num'],$c['start_time'],$c['end_time']);
			}
			$sql_broken = "select id,units_id,units_num,renew_type,death_time from aw_units_broken_map where cid='{$cid}'";
			$broken = $db->vals($sql_broken,true);
			foreach($broken as $b){
				$val[2][$b['renew_type']][$b['id']] = array($b['units_id'],$b['units_num'],$b['death_time']);
				$val[4][$b['id']] = $b['renew_type'];
			}
			$sql_space = "select id,_type,units_id,_nickname,start_time,end_time,units_num from aw_space_map where cid='{$cid}'";
			$space = $db->vals($sql_space,true);
			foreach($space as $s){
// 				$val[3][0][$s['_type']][$s['units_id']][] = array($s['id'],$s['units_num'],$s['_nickname'],$s['start_time'],$s['end_time']);
// 				$val[3][1][$s['id']] = array($s['_type'],$s['units_id']);
				$val[3][0][$s['id']] = array($s['_type'],$s['units_id'],$s['units_num'],$s['_nickname'],$s['start_time'],$s['end_time']);
				$val[3][1][$s['_type']][$s['units_id']][] = $s['id'];
			}
			$sql_transport = "select id,units_id,units_num,_arrive from aw_units_transport where cid='{$cid}'";
			$transport = $db->vals($sql_transport,true);
			foreach($transport as $t){
				$val[5][] = array($t['units_id'],$t['units_num'],$t['_arrive'],$t['id']);
			}
			
			$cache->set($key, $val);
		}
		return $val;
	}
	function aw_units_map_sql($cid){
		return CACHE_KEY_UNITS_MAP."'{$cid}'";
	}
	define("CACHE_KEY_USER","select cid,_role,_nickname,_continents,_country,_ground_style,_times,_index,_line,_rice,_wood,_stone,_oil,_rmb,_lv,_exp,_fight_point,_money,_max_storage,_peop_max,_peop_now,_status,_color,_re,actionPower,growthPower,_vip,_lock from aw_user");
	define("CACHE_KEY_USER_INDEX","select cid,_nickname,_country,uid from aw_user");
	/**
	 * 按角色ID返回角色信息
	 * $val[0] = array("_role","_nickname","_continents","_country","_ground_style","_times","_index","_line","_rice","_wood","_stone","_oil","_rmb","_lv","_exp","_fight_point","_money","_max_storage","_peop_max","_peop_now","_status","_color");
	 * 0 = "_role"	<br>
	 * 1 = "_nickname"	<br>
	 * 2 = "_continents"	<br>
	 * 3 = "_country"	<br>
	 * 4 = "_ground_style"	<br>
	 * 5 = "_times"	<br>
	 * 6 = "_index"	<br>
	 * 7 = "_line"	<br>
	 * 8 = "_rice"	<br>
	 * 9 = "_wood"	<br>
	 * 10 = "_stone"	<br>
	 * 11 = "_oil"	<br>
	 * 12 = "_rmb"	<br>
	 * 13 = "_lv"	<br>
	 * 14 = "_exp"	<br>
	 * 15 = "_fight_point"	<br>
	 * 16 = "_money"	<br>
	 * 17 = "_max_storage"	<br>
	 * 18 = "_peop_max"	<br>
	 * 19 = "_peop_now"	<br>
	 * 20 = "_status"	<br>
	 * 21 = "_color"	<br>
	 * 22 = "_re"	<br>
	 * $val[1] = array("actionPower","growthPower","_vip","_lock");
	 * 0 = "actionPower"	<br>
	 * 1 = "growthPower"	<br>
	 * 2 = "_vip"	<br>
	 * 3 = "_lock"	<br>
	 * @param int $cid
	 * @param int $cas 引用参数，传递出数据的版本号
	 */
	function aw_user($cid,&$cas=null){
		global $cache;
		$sql = aw_user_sql($cid);
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			$u = $db->val($sql);
			$val = array();
			$val[0] = array($u["_role"],$u["_nickname"],$u["_continents"],$u["_country"],$u["_ground_style"],$u["_times"],$u["_index"],$u["_line"],$u["_rice"],$u["_wood"],$u["_stone"],$u["_oil"],$u["_rmb"],$u["_lv"],$u["_exp"],$u["_fight_point"],$u["_money"],$u["_max_storage"],$u["_peop_max"],$u["_peop_now"],$u["_status"],$u["_color"],$u["_re"]);
			$val[1] = array($u["actionPower"],$u["growthPower"],$u["_vip"],$u['_lock']);
			$cache->set($key,$val);
		}
		return $val;
	}
	/**
	 * 用户索引。根据角色名查询角色id
	 * $val['_nickname']=$cid;
	 */
	function aw_user_index(&$cas=null){
		global $cache;
		$sql = aw_user_index_sql();
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			$users = $db->vals($sql,true);
			$val = array();
			foreach($users as $v){
				if(!empty($v['_nickname']))$val[0][$v['_nickname']] = $v['cid'];
				if($v['_country']>0)$val[1][$v['_country']][] = $v['cid'];
				$val[2][$v['uid']] = $v['cid'];
			}
			$cache->set($key,$val);
		}
		return $val;
	}
	function aw_user_index_sql(){
		return CACHE_KEY_USER_INDEX;
	}
	
	function aw_user_sql($cid){
		return CACHE_KEY_USER." where cid='{$cid}'";
	}
	
	define("CACHE_KEY_USER_PROFICIENCY", "select id,cid,_job,building_id,_num from aw_user_proficiency");
	function aw_user_proficiency(&$cas=null){
		global $cache;
		$sql = aw_user_proficiency_sql();
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			$list = $db->vals($sql,true);
			$val = array();
			foreach ($list as $l){
				$val[0][$l['cid']][0][$l['building_id']] = array($l['id'],$l['_job'],$l['_num']);
				$val[0][$l['cid']][1][$l['_job']] = $l['building_id'];
			}
			$cache->set($key, $val);
		}
		return $val;
	}
	function aw_user_proficiency_sql(){
		return CACHE_KEY_USER_PROFICIENCY;
	}
	define("CACHE_KEY_WORLD","select id,_index,_cid,_line from aw_world where _country=");
	/**
	 * 通过线id,坐标查找用户，
	 * $val[0]['_line']['_index']=array(0=>"id",1=>"cid");
	 * 最大区域数
	 * $val[1] = $max_line;
	 */
	function aw_world($country,&$cas=null){
		global $cache;
		$sql = aw_world_sql($country);
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			$army = $db->vals($sql,true);
			$val = array();
			foreach($army as $v){
				$val[0][$v['_line']][$v['_index']] = array($v['id'],$v['_cid']);
				$val[1] = $v['_line']>$val[1]?$v['_line']:$val[1];
			}
			$cache->set($key, $val);
		}
		return $val;
	}
	function aw_world_sql($country){
		return CACHE_KEY_WORLD."'{$country}' order by _line,_index";
	}
	define("CACHE_KEY_WORLD_CHIFE_POSITION", "select id,_country from aw_world where _cid=1 order by id");
	function aw_world_chife_position(&$cas=null){
		global $cache;
		$sql = aw_world_chife_position_sql();
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			
			$list = $db->vals($sql,true);
			$val = array();
			foreach ($list as $v){
				$val[0][] = array($v['id'],$v['_country']);
			}
			
			$cache->set($key,$val);
		}
		
		return $val;
	}
	function aw_world_chife_position_sql(){
		return CACHE_KEY_WORLD_CHIFE_POSITION;
	}
	define("CACHE_KEY_WORLD_ARMY", "select world_id,_lv from aw_world_army");
	/**
	 * $val[0]['world_id'] = $_lv;
	 * 能过地图id查看山贼海盗等级
	 */
	function aw_world_army(&$cas=null){
		global $cache;
		$sql = aw_world_army_sql();
		$key = key_cache($sql);
		$val = $cache->get($key,null,$cas);
		if($cache->getResultCode() == Memcached::RES_NOTFOUND){
			$db = new d5db();
			$db->connect();
			$army = $db->vals($sql,true);
			$val = array();
			foreach($army as $v){
				$val[0][$v['world_id']] = $v['_lv'];
			}
			$cache->set($key, $val);
		}
		return $val;
	}
	function aw_world_army_sql(){
		return CACHE_KEY_WORLD_ARMY;
	}
	
	##########
	#
	# 工具方法
	#
	##########
	/**
	 * 加密缓存key字符串
	 */
	function key_cache($sql){
		return md5($sql);
	}
	
	/**
	 * 将stdClass转换为Array
	 * @param unknown_type $obj
	 * @return unknown
	 */
	function object_to_array($obj){
		$_arr = is_object($obj)?get_object_vars($obj):$obj;
		foreach ($_arr as $key=>$val){
			$val = (is_array($val) || is_object($val)) ? object_to_array($val):$val;
			$arr[$key] = $val;
		}
		return $arr;
	}
	
	

?>