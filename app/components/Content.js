import React from 'react';
import { Switch, Route } from 'react-router-dom';
import Dashboard from './views/Dashboard';
import Login from './views/Login';
import Accounts from './views/Accounts';
import Expenses from './views/Expenses';

const Content = () => (
    <div className="main">
        <Switch>
            <Route exact path='/' component={Dashboard}/>
            <Route path='/login' component={Login}/>
            <Route path='/accounts' component={Accounts}/>
            <Route path='/expenses' component={Expenses}/>
        </Switch>
    </div>
);

export default Content;
