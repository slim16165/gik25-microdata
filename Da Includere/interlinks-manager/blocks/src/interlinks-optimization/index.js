const {registerPlugin} = wp.plugins;
import render from './components/Sidebar';

registerPlugin(
    'daim-interlinks-optimization',
    {
      icon: false,
      render,
    },
);