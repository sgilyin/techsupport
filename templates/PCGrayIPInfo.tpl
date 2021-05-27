<div class='entry'>
    <div class='woo-sc-box normal  rounded full'>
        <table>
            <caption>Gray-IP</caption>
            <tr>
                <td><a class='button' target='_blank' href='https://fialka.tv/techsupport/get-log.php?host={HOST}'>LOG</a> Switch</td>
                <td>Uptime</td>
                <td>{BTN_CHANGE_IF_ADMIN_STATUS} Port</td>
                <td>Uptime</td>
                <td>Link (speed)</td>
                <td>Download</td>
                <td>Upload</td>
                <td><input type='submit' name='btnCableTest' value='Test'> Cable</td>
                <td>Pair A</td>
                <td>Pair B</td>
            </tr>
            <tr>
                <td>{CABLE_TEST_LINK}</td>
                <td>{SYS_UP_TIME}</td>
                <td>{PORT}</td>
                <td>{IF_LAST_CHANGE}</td>
                <td>{IF_OPER_STATUS}{IF_ADMIN_STATUS} ({PORT_SPEED_DPX_STATUS})</td>
                <td>{PORT_OUT_UTIL} Mbps</td>
                <td>{PORT_IN_UTIL} Mbps</td>
                <td>{CABLE_DIAG_RESULT_TIME}</td>
                <td>{CABLE_A_STATUS} ({CABLE_A_DISTANCE})</td>
                <td>{CABLE_B_STATUS} ({CABLE_B_DISTANCE})</td>
            </tr>
        </table>
        <table>
        <caption>MAC-address table</caption>
            <tr>
                <td>VLAN</td>
                <td>IP Address</td>
                <td>Lease</td>
                <td>MAC Address</td>
                <td>MAC Vendor</td>
            </tr>
            {ROWS}
        </table>
        <table>
            <caption>Recent work on the switch</caption>
            <tr>
                <td>Date</td>
                <td>Port</td>
                <td>Worker</td>
            </tr>
            <tr>
                <td>{SWITCH_LAST_DATE}</td>
                <td>{SWITCH_LAST_PORT}</td>
                <td>{SWITCH_LAST_WORKER}</td>
            </tr>
        </table>
    </div>
</div>