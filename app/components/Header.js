import React from 'react'
import { Link } from 'react-router-dom'

const Header = () => (
    <header>
        <nav>
            <ul>
                <li><Link to='/'>Dashboard</Link></li>
                <li><Link to='/accounts'>Accounts</Link></li>
                <li><Link to='/expenses'>Expenses</Link></li>
            </ul>
        </nav>
    </header>
);

export default Header;
