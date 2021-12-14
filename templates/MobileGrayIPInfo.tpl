<div class='entry'>
    <div class='woo-sc-box normal  rounded full'>
        <table>
            <caption>Gray-IP</caption>
            <tr>
                <td><a class='button' target='_blank' href='https://fialka.tv/techsupport/get-log.php?host={HOST}'>Switch LOG</a></td>
                <td>Switch Uptime</td>
            </tr>
                <td>{CABLE_TEST_LINK}</td>
                <td>{SYS_UP_TIME}</td>
            <tr>
            </tr>
        </table>
        <table>
            <tr>
                <td>{BTN_CHANGE_IF_ADMIN_STATUS} Port</td>
                <td>Port Uptime</td>
                <td>Link (speed)</td>
                <td>Usage</td>
            </tr>
            <tr>
                <td>{PORT}</td>
                <td>{IF_LAST_CHANGE}</td>
                <td>{IF_OPER_STATUS}{IF_ADMIN_STATUS} ({IF_SPEED})</td>
                <td>{IF_USAGE}</td>
            </tr>
        </table>
        <table>
            <tr>
                <td><input type='submit' name='btnCableTest' value='Test'> Cable</td>
                <td>Cable Diag</td>
            </tr>
            <tr>
                <td>{CABLE_DIAG}</td>
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