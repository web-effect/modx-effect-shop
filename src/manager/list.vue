<style>
    .app-list th {
        white-space: nowrap;
    }
    .app-list th>.control {
        margin-left: -.625em;
        font-weight: normal;
    }
	.datepicker.control {
		min-width: 14em;
	}

	.app-list .status-cell {
		display: flex;
		align-items: center;
	}
	.app-list .status-cell .status-color {
		margin-right: .6em;
		margin-left: 0;
		width: .8em; height: .8em;
	}
</style>



<template>
<main class="container app-list">
<div class="b-table" :class="{ 'is-loading': loading }">
	
	<div class="table-wrapper">
		
		<table class="table has-mobile-cards">
					
			<thead>

				<tr>
					<th></th>
					<th></th>
					<template v-for="col in columns">
					<th @click="sorting(col.name)" class="is-sortable"  :width="col.width || 'auto'">
						<div :class="{ 'is-numeric' : col.numeric }" class="th-wrap">
							{{ col.label }}
							<b-icon v-if="filter.sortField==col.name" :class="{'is-desc': filter.sortDir=='DESC'}" size="is-small" icon="arrow-up"></b-icon>
						</div>
					</th>
					</template>
				</tr>

				<tr>
					<th></th>
					<th></th>
					<th>
						<b-datepicker range
							:value="getDates(filter.dates)"
							@input="setDates($event)"
						>
							<b-button type="is-danger" icon-left="close" @click="addFilter('dates', [])"></b-button>
						</b-datepicker>
					</th>
					<th>
						<b-select @input="addFilter('status', $event)" v-model="filter.status">
							<option></option>
							<option v-for="s in sets.statuses" :value="s.key">{{ s.label }}</option>
						</b-select>
					</th>
					<th>
						<b-select @input="addFilter('payment', $event)" v-model="filter.payment">
							<option></option>
							<option v-for="i,k in sets.payment" :value="i.key">{{ i.label }}</option>
						</b-select>
					</th>
					<th>
						<b-select @input="addFilter('delivery', $event)" v-model="filter.delivery">
							<option></option>
							<option v-for="i,k in sets.delivery" :value="i.key">{{ i.label }}</option>
						</b-select>
					</th>
					<th></th>
					<th>
						<b-input @input="addFilter('contacts__email', $event, 1)" v-model.trim="filter['contacts__email']"></b-input>
					</th>
				</tr>

			</thead>
			
			<tbody>
				<tr v-for="row,index in rows">
					<td class="checkbox-cell">
						<b-checkbox></b-checkbox>
					</td>
					<td>
						<b-button tag="router-link" :to="'list/' + row.id"
							icon-left="eye"
						></b-button>
					</td>

					<td :data-label="columns[0].label">
						#{{ row.id }} —
						{{ row.date }}
					</td>

					<td :data-label="columns[1].label">
						<div class="status-cell">
							<span class="status-color" :style="{ background: row.status_color }"></span>
							{{ row.status_name }}
						</div>
					</td>

	

					<td :data-label="columns[2].label">
						{{ row.payment_name }}
					</td>

					<td :data-label="columns[3].label">
						{{ row.delivery_name }}
					</td>

					<td :data-label="columns[4].label" class="has-text-right">
						{{ row.total_price | price }}
					</td>

					<td :data-label="columns[5].label">
						<div class="level top">
							<template v-if="row.contacts">
								{{ row.contacts.email }}
							</template>
							<div v-if="row.userid > 0">
								<b-icon icon="account" size="is-small"></b-icon>
								{{ row.userid }}
							</div>
						</div>
					</td>
					
				</tr>
			
			</tbody>
			
		</table>
		
		<template v-if="!rows.length">
			<div class="section content has-text-grey has-text-centered" v-if="!loading">
				<p><b-icon icon="emoticon-sad" size="is-large"></b-icon></p>
				<p>Ничего не найдено</p>
			</div>
		</template>
	
	</div>
	
	<div class="level">	  
		<b-pagination
			@change="paginate($event)"
			:total="total"
			:current.sync="page"
			:per-page="limit"
		></b-pagination>
	</div>


</div>
</main>
</template>

<script src="./list.js"></script>