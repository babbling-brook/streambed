module.exports = {
  tableName: 'lookup',
  autoPK : false,
  autoCreatedAt: false,
  autoUpdatedAt: false,
  attributes: {
    lookup_id: {
      type: 'integer',
      unique: true,
      primaryKey: true,
      autoIncrement: true,
      columnName: 'lookup_id'
    },
    domain: {
      type: 'string',
      columnName: 'column_name',
      size: 127
    },
    domain: {
      type: 'string',
      columnName: 'value',
      size: 127
    },
    domain: {
      type: 'string',
      columnName: 'description'
    },
    domain: {
      type: 'integer',
      columnName: 'sort_order'
    },
  }
};
