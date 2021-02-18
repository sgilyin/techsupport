<div class='entry'>
    <div class='woo-sc-box normal  rounded full'>
        <table>
            <caption>GePON OLT</caption>
            <tr>
                <td><a class='button' target='_blank' href='https://fialka.tv/techsupport/get-log.php?host={HOST}'>LOG</a> Switch</td>
                <td>Uptime</td>
                <td>Admin status</td>
                <td>Oper status</td>
                <td>RX Power</td>
            </tr>
            <tr>
                <td>{HOST}</td>
                <td>{SYS_UP_TIME}</td>
                <td>{IF_ADMIN_STATUS}</td>
                <td>{IF_OPER_STATUS}</td>
                <td>{OLT_RX_POWER} DBm</td>
            </tr>
        </table>
        <table>
            <caption>GePON ONU</caption>
            <tr>
                <td>Port (MAC)</td>
                <td>Status</td>
                <td>Uptime</td>
                <td>Reason</td>
                <td>RX Power</td>
                <td>TX Power</td>
                <td>CTV Power</td>
            </tr>
            <tr>
                <td><a href="{GRAPH_LINK}" target="_blank" >{PORT_MAC}</a></td>
                <td>{ONU_STATUS}</td>
                <td>{IF_LAST_CHANGE}</td>
                <td>{ONU_DEREG_REASON}</td>
                <td>{ONU_RX_POWER} DBm</td>
                <td>{ONU_TX_POWER} DBm</td>
                <td>{ONU_CTV_POWER} DBm</td>
            </tr>
        </table>
        <table>
        <caption>MAC-address table</caption>
            <tr>
                <td>VLAN</td>
                <td>IP Address</td>
                <td>Lease</td>
                <td>MAC-Address</td>
                <td>MAC Vendor</td>
            </tr>
            {ROWS}
        </table>
    </div>
</div>