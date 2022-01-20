<div class='entry'>
    <div class='woo-sc-box normal  rounded full'>
        <form method='post'>
            <input type='hidden' name='bx[address]' value='{ADDRESS}'>
            <input type='hidden' name='bx[phone]' value='{PHONE}'>
            <table>
                <tbody>
                    <tr>
                        <td>Тип задачи</td>
                    </tr>
                    <tr>
                        <td>
                            <input type='radio' name='bx[type]' value='ЗК' id='bxTypeZK'>
                            <label for='bxTypeZK'>ЗК</label>
                            <input type='radio' name='bx[type]' value='СКНП' id='bxTypeSKNP'>
                            <label for='bxTypeSKNP'>СКНП</label>
                            <input type='radio' name='bx[type]' value='Speedtest' id='bxTypeSpeedtest'>
                            <label for='bxTypeSpeedtest'>Speedtest</label>
                            <input type='radio' name='bx[type]' value='Роутер' id='bxTypeRouter'>
                            <label for='bxTypeRouter'>Роутер</label>
                            <input type='radio' name='bx[type]' value='CTV' id='bxTypeCTV'>
                            <label for='bxTypeCTV'>КТВ</label>
                        </td>
                    </tr>
                    <tr>
                        <td>Дата и время</td>
                    </tr>
                    <tr>
                        <td>
                            <input type='radio' name='bx[halfDay]' value='9' id='bxHalfDay1'>
                            <label for='bxHalfDay1'>9-12</label>
                            <input type='radio' name='bx[halfDay]' value='14' id='bxHalfDay2'>
                            <label for='bxHalfDay2'>14-17</label>
                            <input type='date' name='bx[date]'>
                            <label for='preCall'>Предварительный звонок</label>
                            <input type='checkbox' name='bx[preCall]' id='preCall'>
                            <input type='text' name='bx[minPreCall]' id='minPreCall'>
                            <label for='minPreCall'>минут</label>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for='description'>Дополнительный комментарий, если необходим</label>
                            <input type='text' name='bx[description]' id='description'>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type='submit' name='createBxTask' value='Создать задачу в Битрикс'>
                            <input type='reset'>
                        </td>
                    </tr>
                </tbody>
            </table>
    </div>
</div>