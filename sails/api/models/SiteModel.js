module.exports = {
  tableName: 'site',
  autoPK : false,
  autoCreatedAt: false,
  autoUpdatedAt: false,
  attributes: {
    site_id: {
      type: 'integer',
      unique: true,
      primaryKey: true,
      autoIncrement: true,
      columnName: 'site_id',
    },
    domain: {
      type: 'string',
      columnName: 'domain',
      size: 255
    },
  }
};
