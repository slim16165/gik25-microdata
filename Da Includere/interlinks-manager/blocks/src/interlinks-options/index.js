const {registerPlugin} = wp.plugins;
import render from './components/Sidebar';

registerPlugin(
    'daim-interlinks-options',
    {
      icon: false,
      render,
    },
);