{set $css_td='style="padding:5px 10px; border:1px solid #d6d6d6;"'}
{set $css_th='style="padding:5px 10px; border:1px solid #d6d6d6; text-align:left"'}


<!DOCTYPE html>
<html>
<body>
<table style="border-collapse:collapse; border:1px solid #e5e5e5;">
    <tr>
        <td class="header" style="padding: 0 20px; background:#e5e5e5;">
            <h3 style="text-align: center;">
                {block 'title'}
                    {$subject}
                {/block}
            </h3>
        </td>
    </tr>
    <tr>
        <td class="main" style="padding: 15px 30px 30px;">
                        
            <p style="text-align: left;">Заказ № {$order.id} от {$order.date}</p>
            {block 'text'}{/block}
            
            {if $mode=='status'}
                <p>Статус: {$order.status_name}</p>
                {if $comment}
                    <p>Комментарий менеджера: {$comment}</p>
                {/if}
            {/if}
            
            
            <h3 style="text-align:left; margin-top:20px; margin-bottom:10px;">Состав заказа</h3>
            <table style="width: 100%; border-collapse:collapse;">
                
                <thead>
                <tr>
                    <th {$css_th}>№</th>
                    <th {$css_th}>Наименование</th>
                    <th {$css_th}>Кол-во</th>
                    <th {$css_th}>Цена</th>
                </tr>
                </thead>
                
                <tbody>
                    {foreach $order.items as $k=>$i}
                        <tr class="cart__row">
                            <td {$css_td}>{$k+1}</td>
                            <td {$css_td}>
                                <div>{$i.name}</div>
                                {if $i.variation}
                                    <div><small>{$i.variations[$i.variation].name}</small></div>
                                {/if}
                                {if $i.options}
                                    {foreach $i.options as $opt}
                                        <div><small>{$opt}</small></div>
                                    {/foreach}
                                {/if}
                            </td>
                            <td {$css_td}>{$i.qty}</td>
                            <td {$css_td}>{($i.initial_price ?: $i.price)|num_format} руб.</td>
                        </tr>
                        {if $i.addons}
                            {foreach $i.addons as $k=>$a}
                                {if $a.qty}
                                    <tr style="color: #4f4f4f; font-size: 0.875em;">
                                        <td {$css_td}></td>
                                        <td {$css_td}>+ {$a.name}</td>
                                        <td {$css_td}>{$a.qty}</td>
                                        <td {$css_td}>{$a.price|num_format} руб.</td>
                                    </tr>
                                {/if}
                            {/foreach}
                        {/if}
                    {/foreach}
                </tbody>
                
                
                <tfoot>
                    
                    
                    <tr>
                        <td colspan="3" style="text-align: right; padding:5px 10px; border:1px solid #d6d6d6;">
                            <b>Итого:</b>
                        </td>
                        <td {$css_td}>
                            {$order.total_price|num_format} руб.
                        </td>
                    </tr>
                    
                    {if $order.delivery}
                    <tr>
                        <td colspan="3" style="text-align: right; padding:5px 10px; border:1px solid #d6d6d6;">
                            Доставка
                        </td>
                        <td {$css_td}>
                            {$order.delivery_name ?: $order.delivery}
                        </td>
                    </tr>
                    {/if}
                    {if $order.payment}
                    <tr>
                        <td colspan="3" style="text-align: right; padding:5px 10px; border:1px solid #d6d6d6;">
                            Оплата
                        </td>
                        <td {$css_td}>
                            {$order.payment_name ?: $order.payment}
                        </td>
                    </tr>
                    {/if}
                
                </tfoot>
                
            </table>
            
            
            <h3 style="text-align:left; margin-top:20px; margin-bottom:10px;">Контактные данные</h3>
            <table style="width: 100%; border-collapse:collapse;">
                <colgroup>
                    <col width="20%">
                    <col width="90%">
                </colgroup>
                <tbody>
                    {foreach $order.contacts as $k=>$v}
                        <tr>
                            <td {$css_td}>{$order.contact_fields[$k]}</td>
                            <td {$css_td}>{$v}</td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
                        
        </td>
    </tr>
    
    <tr>
        <td class="footer" style="text-align:center; padding:10px; background:#e5e5e5;">
            <a href="[[++site_url]]" target="_blank">[[++site_url]]</a>
        </td>
    </tr>
    
    
</table>
</body>
</html>