<div class="container">
    <h1 class="display-1">CS631 Banking App</h1>

    <hr />

    <h1 class="display-4">Transaction Application</h1>
    <p class="lead">In fact, every transaction in the bank has a counterpart. When an account is debited, another must be credited. When a customer makes a cheque deposit for example, this credits their account but the account from which the money is taken from must be debited. To keep things simple, we will allow only cheques within the bank on accounts that are listed in the CS631-BANK database.  When a customer withdraws money, his account is debited, and the counterpart is a manual operation (a form to ﬁll out and sign) that is not registered in the database. Same for cash deposit, the customer account is credited, and the counterpart transaction is a record of deposit given to the user. The bank has special accounts for management purpose. One of these accounts is CHARGE that collects all the charges on customer’s accounts. After a transaction, the balance of the account is updated. For debit transactions, be sure that the balance of the account allows the transaction. Otherwise, the transaction is not allowed, and the customer is charged. Each month, a service charge ($10.00) is applied on all the accounts. You will have to design the interface for inserting this data into the database. The database should be updated accordingly after each transaction to reflect the balance of the accounts involved.</p>
    <a class="btn btn-primary" href="#" role="button">Go to Transaction Application</a>

    <hr />

    <h1 class="display-4">Passbook Application</h1>
    <p class="lead">This program is used to print passbooks for customers. The passbook starts by a balance forward, the balance of the account the day of the latest passbook update. Then, it lists all the entries in order since. The program, invoked by a one-word command or a click on the main form, should ﬁrst issue prompts to obtain the account number and/or the customer’s name. Then the inquiry program displays the transaction information resulting from retrieving the data. The program should display the account number and owners and a list of transactions as follow:</p>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Date</th>
                <th>Transaction Code</th>
                <th>Transaction Name</th>
                <th>Debits</th>
                <th>Credits</th>
                <th>Balance</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>31 SEP</td>
                <td>-</td>
                <td>Balance Forward</td>
                <td>-</td>
                <td>-</td>
                <td>1700.00</td>
            </tr>
            <tr>
                <td>1 OCT</td>
                <td>WD</td>
                <td>Withdrawal</td>
                <td>800.00</td>
                <td>-</td>
                <td>900.00</td>
            </tr>
            <tr>
                <td>2 OCT</td>
                <td>SC</td>
                <td>Service Charge</td>
                <td>2.00</td>
                <td>-</td>
                <td>898.00</td>
            </tr>
            <tr>
                <td>3 OCT</td>
                <td>CD</td>
                <td>Customer Deposit</td>
                <td>-</td>
                <td>200.00</td>
                <td>1098.00</td>
            </tr>
        </tbody>
    </table>
    <a class="btn btn-primary" href="#" role="button">Go to Passbook Application</a>

    <hr />

    <h1 class="display-4">Customer Application</h1>
    <p class="lead">This program helps employees of CS631-BANK to create, delete, and modify a customer. To be considered as a customer of the bank, you must open an account. The customer must make a deposit of at least $500 to open an account.</p>
    <a class="btn btn-primary" href="#" role="button">Go to Customer Application</a>
</div>

<hr />

<div class="container">
    <h1>DB Test</h1>
    <?php include 'connection.php' ?>
    <?php $db = dbConn::getConnection() ?>
</div>