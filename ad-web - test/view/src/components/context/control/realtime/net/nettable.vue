<template>
	<div class="port-table">
		<table>
			<thead class="thead-bottom-line">
				<tr>
					<th>{{$t("netStatus.portTableTh1")}}</th>
					<th style='width:60px'>{{$t("netStatus.portTableTh2")}}</th>
					<th>{{$t("netStatus.portTableTh3")}}</th>
					<th>{{$t("netStatus.portTableTh4")}}</th>
					<th>{{$t("netStatus.portTableTh5")}}</th>
					<th>{{$t("netStatus.portTableTh6")}}</th>
				</tr>
			</thead>
			<tbody v-for='(item,index) in eths' :key="index">
				<tr>
					<td rowspan='2'>{{item.name?item.name:'-'}}</td>
					<td rowspan='2' style='width:50px;'>
						<img v-if='item.status' src="../../../../img/portlight.png">
						<img v-else src="../../../../img/portgray.png" />
					</td>
					<td>{{item.outbytes?item.outbytes:0}}</td>
					<td>{{item.outpkts?item.outpkts:0}}</td>
					<td>{{item.out_err?item.out_err:0}}</td>
					<td>{{item.out_drop?item.out_drop:0}}</td>
				</tr>
				<tr>
					<td>{{item.inbytes?item.inbytes:0}}</td>
					<td>{{item.inpkts?item.inpkts:0}}</td>
					<td>{{item.in_err?item.in_err:0}}</td>
					<td>{{item.in_drop?item.in_drop:0}}</td>
				</tr>
			</tbody>
		</table>
	</div>
</template>
<script>
export default {
  props: {
    eths: {
      type: Array,
      required: true
    }
  }
}
</script>
