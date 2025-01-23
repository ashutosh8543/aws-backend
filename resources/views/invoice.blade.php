<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
</head>
<style>
    *{
        padding:0;
        margin:0;
        box-sizing: border-box;
    }
    p{
        font-size:12px;
    }
    main{
        display:flex;
        justify-content: center;
        align-items: center;
        /* min-height: 100vh; */
    }
    main .receipt{
        width: 50%;
        border:1px solid #eee;
        border-radius: 4px;
        padding:0 8px;
        margin:15px;
    }
    .section{
        border-bottom:1px dotted #333;
        padding:6px 0;
    }
    .text-center{
        text-align: center;
    }
    .ml-3{
        margin-left:15px;
    }
    table{
        width: 100%;
        border-collapse: collapse;
    }

    .tabletitle{
    border-bottom: 1px dotted #333;
    }
    .tabletitle td{
        padding:4px 0;
    }

    

</style>
<body style="margin:0;padding:0;" onload="window.print();">
    <main>
        <div class="receipt">
            <div class="section">
                <p class="text-center"><strong>Customer Copy</strong></p><br>
                <p class="text-center">YSQ International</p>
                <p class="text-center">Indonesia</p>
                <p class="text-center">Phone: +62-8808096543</p>
            </div>
            <div class="section">
                <p>Order ID: #YSQ111</p>
                <p>Date: 17-10-2024</p>
                    <p>Name:Tesla </p>
                    <p>Email: tesla@gmail.com</p>
                    <p>Mobile number: +62-88080965432</p>
            </div>
                <div id="table">
                    <table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;background:#ffffff;">
                        <tr class="tabletitle">
                            <td class="item"><p>Item</p></td>
                            <td class="qty" align="center"><p>Qty</p></td>
                            <td class="amt" align="right"><p>Amt</p></td>
                        </tr>
                        <tr>
                            <td style="padding:2px;"></td>
                            <td style="padding:2px;"></td>
                            <td style="padding:2px;"></td>
                        </tr>                        
                        <?php 
                            $total_quantity = 0;
                        ?>
                     
                                                
                            <tr class="product">
                                <td class="tableitem"><p>SAAT M'LD</p></td>
                                <td class="tableitem" align="center"><p>2</p></td>
                                <td class="tableitem" align="right"><p>Rp 600</p></td>
                            </tr>

                        <tr>
                            <td style="padding:2px;"></td>
                            <td style="padding:2px;"></td>
                            <td style="padding:2px;"></td>
                        </tr>
                        <tr style="border-top: 1px dotted #333;">
                            <td style="padding:2px;"></td>
                            <td style="padding:2px;"></td>
                            <td style="padding:2px;"></td>
                        </tr>
                        <tr class="total-qty" >
                            <td class="tableitem"><p>Total Qty:</p></td>
                            <td class="tableitem" align="center"><p></p></td>
                            <td class="tableitem" align="right">2</td>
                        </tr>
                        <tr class="sub-total">
                            <td class="tableitem"><p>Sub Total:</p></td>
                            <td class="tableitem" align="center"></td>
                            <td class="tableitem" align="right"><p>Rp 600</p></td>
                        </tr>
                        
                        <!-- <tr class="sub-total">
                            <td class="tableitem"><p>Discount:</p></td>
                            <td class="tableitem" align="center"></td>
                            <td class="tableitem" align="right"><p>100</p></td>
                        </tr> -->
                     
                        
                        <tr>
                            <td style="padding:2px;"></td>
                            <td style="padding:2px;"></td>
                            <td style="padding:2px;"></td>
                        </tr>
                        <tr style="border-top: 1px dotted #333;">
                            <td style="padding:2px;"></td>
                            <td style="padding:2px;"></td>
                            <td style="padding:2px;"></td>
                        </tr>
                        <tr>
                            <td style="padding:2px;"></td>
                            <td style="padding:2px;"></td>
                            <td style="padding:2px;"></td>
                        </tr>
                        <tr class="g-amt">
                            <td class="tableitem"><p>Total Amount:</p></td>
                            <td class="tableitem" align="center"></td>
                            <td class="tableitem" align="right"><p>Rp 600</p></td>
                        </tr>
                        
                      
                        
                        <tr class="round-off">
                            <td class="tableitem"><p>Payment method:</p></td>
                            <td class="tableitem" align="center"></td>
                            <td class="tableitem" align="right"><p>Cash</p></td>
                        </tr>
                        <tr>
                            <td style="padding:2px;"></td>
                            <td style="padding:2px;"></td>
                            <td style="padding:2px;"></td>
                        </tr>
                        <tr style="border-top: 1px dotted #333;">
                            <td style="padding:2px;"></td>
                            <td style="padding:2px;"></td>
                            <td style="padding:2px;"></td>
                        </tr>
                    </table>
                </div>
                <p style="padding-bottom:8px;text-align:center;">Thank you, visit again!</p>
            </div>
    </main>
</body>
</html>